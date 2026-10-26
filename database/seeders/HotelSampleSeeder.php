<?php

namespace Database\Seeders;

use App\Models\Accommodation;
use App\Models\Hotel;
use App\Models\RoomType;
use Illuminate\Database\Seeder;

/** Carga el hotel de ejemplo Decameron Cartagena. */
class HotelSampleSeeder extends Seeder
{
    /** Crea o actualiza el hotel y sus tres acomodaciones. */
    public function run(): void
    {
        $types = RoomType::query()->pluck('id', 'name');
        $accommodations = Accommodation::query()->pluck('id', 'name');

        $hotel = Hotel::query()->updateOrCreate(
            ['nit' => '12345678-8'],
            [
                'name' => 'DECAMERON CARTAGENA',
                'address' => 'CALLE 23 58-25',
                'city' => 'CARTAGENA',
                'room_count' => 42,
            ]
        );

        $hotel->accommodations()->delete();
        $hotel->accommodations()->createMany([
            [
                'room_type_id' => $types['Estándar'],
                'accommodation_id' => $accommodations['Sencilla'],
                'quantity' => 25,
            ],
            [
                'room_type_id' => $types['Junior'],
                'accommodation_id' => $accommodations['Triple'],
                'quantity' => 12,
            ],
            [
                'room_type_id' => $types['Estándar'],
                'accommodation_id' => $accommodations['Doble'],
                'quantity' => 5,
            ],
        ]);
    }
}
