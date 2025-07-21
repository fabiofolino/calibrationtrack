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
    ];

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function calibrationRecords(): HasMany
    {
        return $this->hasMany(CalibrationRecord::class);
    }
}
