<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Gage extends Model
{
    use HasFactory;

    protected $fillable = [
        'department_id',
        'name',
        'serial_number',
        'frequency_days',
        'next_due_date',
    ];

    protected function casts(): array
    {
        return [
            'next_due_date' => 'date',
        ];
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function calibrationRecords(): HasMany
    {
        return $this->hasMany(CalibrationRecord::class);
    }

    public function checkouts(): HasMany
    {
        return $this->hasMany(GageCheckout::class);
    }

    /**
     * Get the current active checkout for this gage
     */
    public function currentCheckout(): ?GageCheckout
    {
        return $this->checkouts()->whereNull('checked_in_at')->first();
    }

    /**
     * Check if the gage is currently checked out
     */
    public function isCheckedOut(): bool
    {
        return $this->currentCheckout() !== null;
    }

    /**
     * Calculate and update the next due date based on frequency and last calibration
     */
    public function updateNextDueDate(): void
    {
        $lastCalibration = $this->calibrationRecords()->latest('calibrated_at')->first();
        
        if ($lastCalibration) {
            $this->next_due_date = $lastCalibration->calibrated_at->addDays($this->frequency_days);
        } else {
            // If no calibrations, due immediately (or could be null)
            $this->next_due_date = now()->toDateString();
        }
        
        $this->save();
    }

    /**
     * Get the calibration status based on due date
     */
    public function getCalibrationStatus(): string
    {
        if (!$this->next_due_date) {
            return 'unknown';
        }

        $daysUntilDue = now()->diffInDays($this->next_due_date, false);
        
        if ($daysUntilDue < 0) {
            return 'overdue'; // Red
        } elseif ($daysUntilDue <= 30) {
            return 'due_soon'; // Yellow
        } else {
            return 'on_schedule'; // Green
        }
    }

    /**
     * Get the calibration status color class
     */
    public function getCalibrationStatusColor(): string
    {
        return match($this->getCalibrationStatus()) {
            'overdue' => 'text-red-600 bg-red-50',
            'due_soon' => 'text-yellow-600 bg-yellow-50',
            'on_schedule' => 'text-green-600 bg-green-50',
            default => 'text-gray-600 bg-gray-50',
        };
    }

    /**
     * Get the days until due (can be negative if overdue)
     */
    public function getDaysUntilDue(): ?int
    {
        if (!$this->next_due_date) {
            return null;
        }

        return now()->diffInDays($this->next_due_date, false);
    }
}
