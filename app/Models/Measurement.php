<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Measurement extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'measurement_group_id',
        'sequence',
        'nominal',
        'upper_limit',
        'lower_limit',
        'as_found_value',
        'as_found_error',
        'as_found_pass',
        'as_left_value',
        'as_left_error',
        'as_left_pass',
        'description',
        'uncertainty',
        'standards_used',
    ];

    protected function casts(): array
    {
        return [
            'nominal' => 'decimal:6',
            'upper_limit' => 'decimal:6',
            'lower_limit' => 'decimal:6',
            'as_found_value' => 'decimal:6',
            'as_found_error' => 'decimal:6',
            'as_found_pass' => 'boolean',
            'as_left_value' => 'decimal:6',
            'as_left_error' => 'decimal:6',
            'as_left_pass' => 'boolean',
            'uncertainty' => 'decimal:6',
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['nominal', 'as_found_value', 'as_left_value', 'as_found_pass', 'as_left_pass'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    public function measurementGroup(): BelongsTo
    {
        return $this->belongsTo(MeasurementGroup::class);
    }

    /**
     * Calculate and update the as found pass/fail status and error
     */
    public function calculateAsFoundStatus(): void
    {
        if ($this->as_found_value !== null) {
            $this->as_found_error = $this->as_found_value - $this->nominal;
            $this->as_found_pass = $this->as_found_value >= $this->lower_limit && 
                                  $this->as_found_value <= $this->upper_limit;
        }
    }

    /**
     * Calculate and update the as left pass/fail status and error
     */
    public function calculateAsLeftStatus(): void
    {
        if ($this->as_left_value !== null) {
            $this->as_left_error = $this->as_left_value - $this->nominal;
            $this->as_left_pass = $this->as_left_value >= $this->lower_limit && 
                                 $this->as_left_value <= $this->upper_limit;
        }
    }

    /**
     * Get the status color class for as found measurement
     */
    public function getAsFoundStatusColor(): string
    {
        if ($this->as_found_pass === null) {
            return 'bg-gray-100 text-gray-800';
        }
        return $this->as_found_pass ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800';
    }

    /**
     * Get the status color class for as left measurement
     */
    public function getAsLeftStatusColor(): string
    {
        if ($this->as_left_pass === null) {
            return 'bg-gray-100 text-gray-800';
        }
        return $this->as_left_pass ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800';
    }

    /**
     * Check if this measurement is within tolerance
     */
    public function isInTolerance(): bool
    {
        return $this->as_found_pass === true && $this->as_left_pass === true;
    }
}
