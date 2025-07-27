<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Laravel\Cashier\Billable;

class Company extends Model
{
    use HasFactory, Billable;

    protected $fillable = [
        'name',
        'address',
    ];

    protected function casts(): array
    {
        return [
            'trial_ends_at' => 'datetime',
        ];
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function departments(): HasMany
    {
        return $this->hasMany(Department::class);
    }

    public function gages(): HasManyThrough
    {
        return $this->hasManyThrough(Gage::class, Department::class);
    }

    /**
     * Check if the company has an active subscription
     */
    public function hasActiveSubscription(): bool
    {
        return $this->subscribed('default');
    }

    /**
     * Check if the company is on a trial period
     */
    public function onTrial(): bool
    {
        return $this->onGenericTrial();
    }

    /**
     * Check if the company can add more gages
     */
    public function canAddGages(): bool
    {
        // If they have an active subscription, they can add unlimited gages
        if ($this->hasActiveSubscription()) {
            return true;
        }

        // If they're on trial, they can add unlimited gages
        if ($this->onTrial()) {
            return true;
        }

        // Otherwise, check if they're under the 10 gage limit
        return $this->gages()->count() < 10;
    }

    /**
     * Get the current gage count
     */
    public function getGageCount(): int
    {
        return $this->gages()->count();
    }

    /**
     * Get the gage limit for non-subscribers
     */
    public function getGageLimit(): int
    {
        return 10;
    }

    /**
     * Check if the company has reached the gage limit
     */
    public function hasReachedGageLimit(): bool
    {
        return !$this->canAddGages();
    }

    /**
     * Get subscription details for admin
     */
    public function getSubscriptionDetails(): array
    {
        $subscription = $this->subscription('default');
        
        return [
            'has_subscription' => $this->hasActiveSubscription(),
            'on_trial' => $this->onTrial(),
            'trial_ends_at' => $this->trial_ends_at,
            'subscription_status' => $subscription?->stripe_status,
            'subscription_created' => $subscription?->created_at,
            'current_period_end' => $subscription?->asStripeSubscription()?->current_period_end 
                ? date('Y-m-d H:i:s', $subscription->asStripeSubscription()->current_period_end) 
                : null,
            'cancel_at_period_end' => $subscription?->asStripeSubscription()?->cancel_at_period_end ?? false,
            'gage_count' => $this->getGageCount(),
            'gage_limit' => $this->getGageLimit(),
        ];
    }
}
