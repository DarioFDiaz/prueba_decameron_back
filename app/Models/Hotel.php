<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** Representa un hotel y la cantidad máxima de habitaciones que puede tener. */
class Hotel extends Model
{
    protected $fillable = [
        'name',
        'address',
        'city',
        'nit',
        'room_count',
    ];

    /** Guarda el número de habitaciones como entero. */
    protected function casts(): array
    {
        return [
            'room_count' => 'integer',
        ];
    }

    /** Devuelve las acomodaciones configuradas para este hotel. */
    public function accommodations(): HasMany
    {
        return $this->hasMany(HotelAccommodation::class)->orderBy('id');
    }
}
