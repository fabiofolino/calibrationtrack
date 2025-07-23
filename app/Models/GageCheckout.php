<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class GageCheckout extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'gage_id',
        'user_id',
        'checked_out_at',
        'checked_in_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'checked_out_at' => 'datetime',
            'checked_in_at' => 'datetime',
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['gage_id', 'user_id', 'checked_out_at', 'checked_in_at', 'notes'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    public function gage(): BelongsTo
    {
        return $this->belongsTo(Gage::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if this checkout is active (not checked in)
     */
    public function isActive(): bool
    {
        return $this->checked_in_at === null;
    }

    /**
     * Check in this checkout
     */
    public function checkIn(?string $notes = null): void
    {
        $this->update([
            'checked_in_at' => now(),
            'notes' => $notes ? ($this->notes ? $this->notes . "\n\nCheck-in notes: " . $notes : "Check-in notes: " . $notes) : $this->notes,
        ]);
    }
}
