<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/** Catálogo fijo de tipos de habitación: Estándar, Junior y Suite. */
class RoomType extends Model
{
    public $timestamps = false;

    protected $fillable = ['name'];

    /** Acomodaciones que este tipo puede usar. */
    public function accommodations(): BelongsToMany
    {
        return $this->belongsToMany(Accommodation::class, 'room_type_accommodation');
    }
}
