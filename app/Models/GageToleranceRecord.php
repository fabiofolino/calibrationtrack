<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class GageToleranceRecord extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'gage_id',
        'calibration_record_id',
        'user_id',
        'status',
        'risk_assessment',
        'corrective_actions',
        'identified_at',
        'resolved_at',
        'resolution_notes',
    ];

    protected function casts(): array
    {
        return [
            'identified_at' => 'datetime',
            'resolved_at' => 'datetime',
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['status', 'risk_assessment', 'corrective_actions', 'resolved_at', 'resolution_notes'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    public function gage(): BelongsTo
    {
        return $this->belongsTo(Gage::class);
    }

    public function calibrationRecord(): BelongsTo
    {
        return $this->belongsTo(CalibrationRecord::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getStatusColor(): string
    {
        return match($this->status) {
            'out_of_tolerance' => 'text-red-600 bg-red-50',
            'under_review' => 'text-yellow-600 bg-yellow-50',
            'resolved' => 'text-green-600 bg-green-50',
            default => 'text-gray-600 bg-gray-50',
        };
    }

    public function markAsResolved(string $resolutionNotes): void
    {
        $this->update([
            'status' => 'resolved',
            'resolved_at' => now(),
            'resolution_notes' => $resolutionNotes,
        ]);
    }
}
