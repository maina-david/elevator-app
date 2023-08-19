<?php

namespace App\Models;

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
}