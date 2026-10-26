<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** Arma la respuesta JSON de un tipo de habitación. */
class RoomTypeResource extends JsonResource
{
    /**
     * Incluye las acomodaciones permitidas para ese tipo.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'accommodations' => $this->accommodations->map(fn ($accommodation) => [
                'id' => $accommodation->id,
                'name' => $accommodation->name,
            ])->values(),
        ];
    }
}
