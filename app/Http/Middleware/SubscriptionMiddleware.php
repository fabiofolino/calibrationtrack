<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\Response;

class SubscriptionMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Skip subscription check in testing environment
        if (app()->environment('testing')) {
            return $next($request);
        }
        
        $user = $request->user();
        
        // Allow access if user has no company (probably in the registration process)
        if (!$user || !$user->company) {
            return $next($request);
        }

        $company = $user->company;

        // Allow access if the company has an active subscription or is on trial
        if ($company->subscribed('default') || $company->onTrial()) {
            return $next($request);
        }

        // Redirect to subscription page if no active subscription
        return Inertia::render('Subscription/Subscribe', [
            'intent' => $company->createSetupIntent(),
            'stripe_key' => config('cashier.key'),
        ]);
    }
}
