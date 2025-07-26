<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class MeasurementGroup extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'calibration_record_id',
        'group_number',
        'group_name',
        'type',
        'plus_value',
        'minus_value',
        'mask_min',
        'mask_max',
        'units',
        'notes',
        'show_sequence',
        'show_description',
        'auto_fill_after',
        'show_uncertainty',
        'show_standards',
    ];

    protected function casts(): array
    {
        return [
            'show_sequence' => 'boolean',
            'show_description' => 'boolean',
            'auto_fill_after' => 'boolean',
            'show_uncertainty' => 'boolean',
            'show_standards' => 'boolean',
            'plus_value' => 'decimal:6',
            'minus_value' => 'decimal:6',
            'mask_min' => 'decimal:6',
            'mask_max' => 'decimal:6',
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['group_number', 'group_name', 'type', 'plus_value', 'minus_value', 'units'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    public function calibrationRecord(): BelongsTo
    {
        return $this->belongsTo(CalibrationRecord::class);
    }

    public function measurements(): HasMany
    {
        return $this->hasMany(Measurement::class)->orderBy('sequence');
    }

    /**
     * Calculate upper and lower limits for a nominal value based on the group type
     */
    public function calculateLimits(float $nominal): array
    {
        switch ($this->type) {
            case 'tolerance_percent':
                $tolerance = $nominal * ($this->plus_value / 100);
                return [
                    'upper' => $nominal + $tolerance,
                    'lower' => $nominal - $tolerance
                ];
                
            case 'tolerance_plus_minus':
                return [
                    'upper' => $nominal + $this->plus_value,
                    'lower' => $nominal - $this->minus_value
                ];
                
            case 'limits':
                return [
                    'upper' => $this->mask_max ?? $nominal,
                    'lower' => $this->mask_min ?? $nominal
                ];
                
            default:
                return ['upper' => $nominal, 'lower' => $nominal];
        }
    }

    /**
     * Get the overall pass/fail status for this measurement group
     */
    public function getOverallStatus(): string
    {
        $measurements = $this->measurements;
        
        if ($measurements->isEmpty()) {
            return 'no_data';
        }

        $hasFailures = $measurements->where('as_found_pass', false)->count() > 0 ||
                      $measurements->where('as_left_pass', false)->count() > 0;
        
        $hasIncomplete = $measurements->whereNull('as_found_pass')->count() > 0 ||
                        $measurements->whereNull('as_left_pass')->count() > 0;

        if ($hasFailures) {
            return 'fail';
        } elseif ($hasIncomplete) {
            return 'incomplete';
        } else {
            return 'pass';
        }
    }
}
