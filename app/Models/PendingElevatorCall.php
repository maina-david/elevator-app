<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PendingElevatorCall extends Model
{
    use HasFactory;

    protected $fillable = [
        'elevator_id',
        'target_floor',
        'executed',
        'execution_duration'
    ];

    /**
     * Get the elevator that owns the PendingElevatorCall
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function elevator(): BelongsTo
    {
        return $this->belongsTo(Elevator::class);
    }
}