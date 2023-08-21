<?php

namespace App\Models;

use App\Events\ElevatorActionEvent;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ElevatorLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'elevator_id',
        'user_id',
        'current_floor',
        'state',
        'direction',
        'action',
        'details'
    ];

    /**
     * Get the elevator that owns the ElevatorLog
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function elevator(): BelongsTo
    {
        return $this->belongsTo(Elevator::class);
    }

    /**
     * Boot the model.
     *
     * @return void
     */
    protected static function boot()
    {
        parent::boot();

        static::created(function ($elevatorLog) {
            ElevatorActionEvent::dispatch($elevatorLog);
        });
    }
}