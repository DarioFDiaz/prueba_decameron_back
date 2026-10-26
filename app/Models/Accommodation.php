<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/** Catálogo fijo de acomodaciones: Sencilla, Doble, Triple y Cuádruple. */
class Accommodation extends Model
{
    public $timestamps = false;

    protected $fillable = ['name'];

    /** Tipos de habitación que admiten esta acomodación. */
    public function roomTypes(): BelongsToMany
    {
        return $this->belongsToMany(RoomType::class, 'room_type_accommodation');
    }
}
