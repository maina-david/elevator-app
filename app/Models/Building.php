<?php

namespace App\Models;

use App\Events\NewBuildingCreated;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Building extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'number_of_floors'
    ];

    /**
     * Get all of the elevators for the Building
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function elevators(): HasMany
    {
        return $this->hasMany(Elevator::class);
    }

    /**
     * Boot the model.
     *
     * @return void
     */
    protected static function boot()
    {
        parent::boot();

        static::created(function ($building) {
            NewBuildingCreated::dispatch($building);
        });
    }
}
