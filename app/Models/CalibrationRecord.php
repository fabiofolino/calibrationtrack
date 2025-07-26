<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class CalibrationRecord extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'gage_id',
        'measured_value',
        'calibrated_value',
        'calibrated_at',
        'user_id',
        'cert_file',
    ];

    protected function casts(): array
    {
        return [
            'measured_value' => 'decimal:4',
            'calibrated_value' => 'decimal:4',
            'calibrated_at' => 'datetime',
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['gage_id', 'measured_value', 'calibrated_value', 'calibrated_at', 'user_id', 'cert_file'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    protected static function booted(): void
    {
        static::created(function (CalibrationRecord $calibrationRecord) {
            $calibrationRecord->gage->updateNextDueDate();
        });

        static::updated(function (CalibrationRecord $calibrationRecord) {
            $calibrationRecord->gage->updateNextDueDate();
        });

        static::deleted(function (CalibrationRecord $calibrationRecord) {
            $calibrationRecord->gage->updateNextDueDate();
        });
    }

    public function gage(): BelongsTo
    {
        return $this->belongsTo(Gage::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function measurementGroups(): HasMany
    {
        return $this->hasMany(MeasurementGroup::class);
    }

    public function getCertificateUrl(): ?string
    {
        if (!$this->cert_file) {
            return null;
        }

        return route('calibration-records.download-certificate', $this);
    }

    public function hasCertificate(): bool
    {
        return !empty($this->cert_file) && \Storage::disk('public')->exists($this->cert_file);
    }
}
