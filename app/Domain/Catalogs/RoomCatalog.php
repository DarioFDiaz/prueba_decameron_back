<?php

namespace App\Domain\Catalogs;

use App\Models\RoomType;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/** Consulta el catálogo fijo de tipos y acomodaciones. */
class RoomCatalog
{
    /** Devuelve los tipos con las acomodaciones que cada uno permite. */
    public function types(): Collection
    {
        return RoomType::query()
            ->with(['accommodations' => fn ($query) => $query->orderBy('accommodations.id')])
            ->orderBy('id')
            ->get();
    }

    /**
     * Arma las parejas tipo-acomodación que están permitidas.
     *
     * @return array<string, true>
     */
    public function allowedKeys(): array
    {
        return DB::table('room_type_accommodation')
            ->get(['room_type_id', 'accommodation_id'])
            ->mapWithKeys(fn ($row) => [$row->room_type_id.'-'.$row->accommodation_id => true])
            ->all();
    }
}
