<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/** Crea las tablas del proyecto dentro del esquema decameron. */
return new class extends Migration
{
    /** Crea catálogos, hoteles, acomodaciones y carga las combinaciones permitidas. */
    public function up(): void
    {
        Schema::create('room_types', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
        });

        Schema::create('accommodations', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
        });

        Schema::create('room_type_accommodation', function (Blueprint $table) {
            $table->foreignId('room_type_id')->constrained()->restrictOnDelete();
            $table->foreignId('accommodation_id')->constrained()->restrictOnDelete();
            $table->primary(['room_type_id', 'accommodation_id']);
        });

        Schema::create('hotels', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('address')->unique();
            $table->string('city');
            $table->string('nit', 20)->unique();
            $table->unsignedInteger('room_count');
            $table->timestamps();
        });

        Schema::create('hotel_accommodations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hotel_id')->constrained()->cascadeOnDelete();
            $table->foreignId('room_type_id')->constrained()->restrictOnDelete();
            $table->foreignId('accommodation_id')->constrained()->restrictOnDelete();
            $table->unsignedInteger('quantity');
            $table->timestamps();
            $table->unique(
                ['hotel_id', 'room_type_id', 'accommodation_id'],
                'hotel_room_configuration_unique'
            );
        });

        DB::statement('ALTER TABLE hotels ADD CONSTRAINT hotels_room_count_positive CHECK (room_count >= 1)');
        DB::statement('ALTER TABLE hotel_accommodations ADD CONSTRAINT hotel_accommodations_quantity_positive CHECK (quantity >= 1)');

        DB::table('room_types')->insert([
            ['id' => 1, 'name' => 'Estándar'],
            ['id' => 2, 'name' => 'Junior'],
            ['id' => 3, 'name' => 'Suite'],
        ]);

        DB::table('accommodations')->insert([
            ['id' => 1, 'name' => 'Sencilla'],
            ['id' => 2, 'name' => 'Doble'],
            ['id' => 3, 'name' => 'Triple'],
            ['id' => 4, 'name' => 'Cuádruple'],
        ]);

        DB::table('room_type_accommodation')->insert([
            ['room_type_id' => 1, 'accommodation_id' => 1],
            ['room_type_id' => 1, 'accommodation_id' => 2],
            ['room_type_id' => 2, 'accommodation_id' => 3],
            ['room_type_id' => 2, 'accommodation_id' => 4],
            ['room_type_id' => 3, 'accommodation_id' => 1],
            ['room_type_id' => 3, 'accommodation_id' => 2],
            ['room_type_id' => 3, 'accommodation_id' => 3],
        ]);

        DB::statement("SELECT setval(pg_get_serial_sequence('room_types', 'id'), (SELECT MAX(id) FROM room_types))");
        DB::statement("SELECT setval(pg_get_serial_sequence('accommodations', 'id'), (SELECT MAX(id) FROM accommodations))");
    }

    /** Elimina las tablas del proyecto. */
    public function down(): void
    {
        Schema::dropIfExists('hotel_accommodations');
        Schema::dropIfExists('hotels');
        Schema::dropIfExists('room_type_accommodation');
        Schema::dropIfExists('accommodations');
        Schema::dropIfExists('room_types');
    }
};
