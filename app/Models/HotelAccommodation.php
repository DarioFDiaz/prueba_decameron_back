<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Guarda cuántas habitaciones de un tipo y una acomodación tiene un hotel. */
class HotelAccommodation extends Model
{
    protected $fillable = [
        'hotel_id',
        'room_type_id',
        'accommodation_id',
        'quantity',
    ];

    /** Guarda la cantidad como entero. */
    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
        ];
    }

    /** Hotel al que pertenece esta configuración. */
    public function hotel(): BelongsTo
    {
        return $this->belongsTo(Hotel::class);
    }

    /** Tipo de habitación de esta configuración. */
    public function roomType(): BelongsTo
    {
        return $this->belongsTo(RoomType::class);
    }

    /** Acomodación de esta configuración. */
    public function accommodation(): BelongsTo
    {
        return $this->belongsTo(Accommodation::class);
    }
}
