<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/** Punto de entrada de los datos iniciales. */
class DatabaseSeeder extends Seeder
{
    /** Carga el hotel de ejemplo. */
    public function run(): void
    {
        $this->call(HotelSampleSeeder::class);
    }
}
