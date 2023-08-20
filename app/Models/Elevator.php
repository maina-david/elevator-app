<?php

namespace App\Models;

use App\Events\NewElevatorCreated;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Elevator extends Model
{
    use HasFactory;

    protected $fillable = [
        'building_id',
        'name',
        'active'
    ];

    /**
     * Get the building that owns the Elevator
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function building(): BelongsTo
    {
        return $this->belongsTo(Building::class);
    }

    /**
     * Get all of the logs for the Elevator
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function logs(): HasMany
    {
        return $this->hasMany(ElevatorLog::class);
    }

    /**
     * Boot the model.
     *
     * @return void
     */
    protected static function boot()
    {
        parent::boot();

        static::created(function ($elevator) {
            NewElevatorCreated::dispatch($elevator);
            // Create an initial elevator log with default details
            ElevatorLog::create([
                'elevator_id' => $elevator->id,
                'current_floor' => 1,
                'state' => 'idle',
                'action' => 'idle',
                'details' => json_encode(['pending_calls' => []]),
            ]);
        });
    }
}
