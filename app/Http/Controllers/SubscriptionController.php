<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;

class SubscriptionController extends Controller
{
    public function subscribe()
    {
        $company = auth()->user()->company;
        
        return Inertia::render('Subscription/Subscribe', [
            'intent' => $company->createSetupIntent(),
            'stripe_key' => config('cashier.key'),
        ]);
    }

    public function store(Request $request)
    {
        $company = auth()->user()->company;
        
        $request->validate([
            'payment_method' => 'required|string',
        ]);

        try {
            // Create subscription with payment method
            $company->newSubscription('default', config('services.stripe.price_id'))
                ->create($request->payment_method);
                
            return redirect()->route('dashboard')->with('success', 'Subscription created successfully!');
        } catch (\Exception $e) {
            return back()->withErrors(['payment' => 'Payment failed: ' . $e->getMessage()]);
        }
    }
}
