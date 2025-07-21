<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CalibrationRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'gage_id',
        'measured_value',
        'calibrated_value',
        'calibrated_at',
        'user_id',
    ];

    protected function casts(): array
    {
        return [
            'measured_value' => 'decimal:4',
            'calibrated_value' => 'decimal:4',
            'calibrated_at' => 'datetime',
        ];
    }

    public function gage(): BelongsTo
    {
        return $this->belongsTo(Gage::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
