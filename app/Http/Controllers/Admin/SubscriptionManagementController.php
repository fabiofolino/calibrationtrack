<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SubscriptionManagementController extends Controller
{
    /**
     * Display subscription management page (admin only)
     */
    public function index()
    {
        // Check if user is admin
        if (!auth()->user()->isAdmin()) {
            abort(403, 'Only administrators can manage subscriptions.');
        }

        $company = auth()->user()->company;
        $subscriptionDetails = $company->getSubscriptionDetails();

        // Only provide Stripe data if they don't have an active subscription
        $stripeData = [];
        if (!$company->hasActiveSubscription()) {
            $stripeData = [
                'stripeKey' => config('cashier.key'),
                'setupIntent' => $company->createSetupIntent(),
            ];
        }

        return Inertia::render('Admin/SubscriptionManagement', array_merge([
            'subscriptionDetails' => $subscriptionDetails,
            'company' => $company->only(['name', 'created_at']),
        ], $stripeData));
    }

    /**
     * Cancel subscription at period end
     */
    public function cancel(Request $request)
    {
        if (!auth()->user()->isAdmin()) {
            abort(403, 'Only administrators can manage subscriptions.');
        }

        $company = auth()->user()->company;
        
        if (!$company->hasActiveSubscription()) {
            return back()->withErrors(['error' => 'No active subscription to cancel.']);
        }

        try {
            $company->subscription('default')->cancelAtPeriodEnd();
            
            return back()->with('success', 'Subscription will be cancelled at the end of the current billing period.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to cancel subscription: ' . $e->getMessage()]);
        }
    }

    /**
     * Resume subscription
     */
    public function resume(Request $request)
    {
        if (!auth()->user()->isAdmin()) {
            abort(403, 'Only administrators can manage subscriptions.');
        }

        $company = auth()->user()->company;
        $subscription = $company->subscription('default');
        
        if (!$subscription || !$subscription->onGracePeriod()) {
            return back()->withErrors(['error' => 'No subscription to resume or subscription is not in grace period.']);
        }

        try {
            $subscription->resume();
            
            return back()->with('success', 'Subscription has been resumed successfully.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to resume subscription: ' . $e->getMessage()]);
        }
    }

    /**
     * Extend trial period (for admin use)
     */
    public function extendTrial(Request $request)
    {
        if (!auth()->user()->isAdmin()) {
            abort(403, 'Only administrators can manage subscriptions.');
        }

        $request->validate([
            'days' => 'required|integer|min:1|max:90',
        ]);

        $company = auth()->user()->company;
        
        try {
            if ($company->onTrial()) {
                // Extend existing trial
                $company->trial_ends_at = $company->trial_ends_at->addDays($request->days);
            } else {
                // Start new trial
                $company->trial_ends_at = now()->addDays($request->days);
            }
            
            $company->save();
            
            return back()->with('success', "Trial period extended by {$request->days} days.");
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to extend trial: ' . $e->getMessage()]);
        }
    }

    /**
     * Store subscription (create new subscription)
     */
    public function store(Request $request)
    {
        if (!auth()->user()->isAdmin()) {
            abort(403, 'Only administrators can manage subscriptions.');
        }

        $company = auth()->user()->company;
        
        // Check if company already has an active subscription
        if ($company->hasActiveSubscription()) {
            return back()->withErrors(['error' => 'Company already has an active subscription.']);
        }
        
        $request->validate([
            'payment_method' => 'required|string',
        ]);

        try {
            // Create subscription with payment method
            $company->newSubscription('default', config('services.stripe.price_id'))
                ->create($request->payment_method);
                
            return back()->with('success', 'Subscription created successfully!');
        } catch (\Exception $e) {
            return back()->withErrors(['payment' => 'Payment failed: ' . $e->getMessage()]);
        }
    }
}