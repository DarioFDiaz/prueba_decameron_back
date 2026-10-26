<?php

namespace App\Domain\Hotels;

use App\Domain\Catalogs\RoomCatalog;
use Illuminate\Validation\Validator;

/** Comprueba las reglas de negocio de las acomodaciones de un hotel. */
class AccommodationRules
{
    public function __construct(private RoomCatalog $catalog) {}

    /**
     * Agrega errores si hay combinaciones repetidas, no permitidas o que superan el máximo.
     *
     * @param  array<int, array<string, mixed>>  $rows
     */
    public function apply(Validator $validator, array $rows, int $roomCount): void
    {
        $allowed = $this->catalog->allowedKeys();
        $seen = [];
        $configured = 0;

        foreach ($rows as $index => $row) {
            $roomTypeId = $row['room_type_id'] ?? null;
            $accommodationId = $row['accommodation_id'] ?? null;
            $key = $roomTypeId.'-'.$accommodationId;

            if (isset($seen[$key])) {
                $validator->errors()->add(
                    "accommodations.$index.room_type_id",
                    'El tipo de habitación y la acomodación no se pueden repetir en el mismo hotel.'
                );
            }

            $seen[$key] = true;

            if (! isset($allowed[$key])) {
                $validator->errors()->add(
                    "accommodations.$index.accommodation_id",
                    'Esta acomodación no está permitida para el tipo de habitación seleccionado.'
                );
            }

            $configured += (int) ($row['quantity'] ?? 0);
        }

        if ($configured > $roomCount) {
            $validator->errors()->add(
                'accommodations',
                'La cantidad de habitaciones configuradas no puede superar el máximo del hotel.'
            );
        }
    }
}
