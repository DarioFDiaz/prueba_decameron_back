<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** Arma la respuesta JSON de un hotel. */
class HotelResource extends JsonResource
{
    /**
     * Convierte el hotel y sus acomodaciones al formato que consume el front.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'address' => $this->address,
            'city' => $this->city,
            'nit' => $this->nit,
            'room_count' => $this->room_count,
            'configured_rooms' => (int) ($this->configured_rooms ?? $this->accommodations->sum('quantity')),
            'accommodations' => $this->whenLoaded('accommodations', function () {
                return $this->accommodations->map(fn ($row) => [
                    'id' => $row->id,
                    'quantity' => $row->quantity,
                    'room_type' => [
                        'id' => $row->roomType->id,
                        'name' => $row->roomType->name,
                    ],
                    'accommodation' => [
                        'id' => $row->accommodation->id,
                        'name' => $row->accommodation->name,
                    ],
                ])->values();
            }),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
