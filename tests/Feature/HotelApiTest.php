<?php

namespace Tests\Feature;

use App\Domain\Nit\NitVerifier;
use App\Models\Accommodation;
use App\Models\RoomType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Recorre la API como lo haría el frontend.
 * Cada prueba usa el esquema decameron_testing y se borra al terminar.
 */
class HotelApiTest extends TestCase
{
    use RefreshDatabase;

    /** El catálogo solo ofrece las acomodaciones permitidas para cada tipo. */
    public function test_room_types_expose_only_allowed_accommodations(): void
    {
        $response = $this->getJson('/api/room-types');

        $response->assertOk()
            ->assertJsonPath('data.0.name', 'Estándar')
            ->assertJsonPath('data.0.accommodations.0.name', 'Sencilla')
            ->assertJsonPath('data.0.accommodations.1.name', 'Doble')
            ->assertJsonPath('data.1.name', 'Junior')
            ->assertJsonPath('data.1.accommodations.0.name', 'Triple')
            ->assertJsonPath('data.1.accommodations.1.name', 'Cuádruple')
            ->assertJsonPath('data.2.name', 'Suite')
            ->assertJsonCount(3, 'data.2.accommodations');
    }

    /** Crea el hotel de ejemplo cuando las cantidades suman exactamente el máximo. */
    public function test_it_creates_a_hotel_when_configured_rooms_are_within_the_limit(): void
    {
        $response = $this->postJson('/api/hotels', $this->payload([
            'room_count' => 42,
            'accommodations' => [
                $this->row('Estándar', 'Sencilla', 25),
                $this->row('Junior', 'Triple', 12),
                $this->row('Estándar', 'Doble', 5),
            ],
        ]));

        $response->assertCreated()
            ->assertJsonPath('data.name', 'DECAMERON CARTAGENA')
            ->assertJsonPath('data.configured_rooms', 42)
            ->assertJsonCount(3, 'data.accommodations');

        $this->assertDatabaseCount('hotels', 1);
    }

    /** Permite guardar el hotel si la suma queda por debajo del máximo. */
    public function test_configured_rooms_may_be_lower_than_the_hotel_maximum(): void
    {
        $this->postJson('/api/hotels', $this->payload([
            'room_count' => 42,
            'accommodations' => [$this->row('Estándar', 'Sencilla', 10)],
        ]))->assertCreated()
            ->assertJsonPath('data.configured_rooms', 10);
    }

    /** Rechaza el hotel si las cantidades superan el número de habitaciones. */
    public function test_configured_rooms_cannot_exceed_the_hotel_maximum(): void
    {
        $this->postJson('/api/hotels', $this->payload([
            'room_count' => 42,
            'accommodations' => [$this->row('Estándar', 'Sencilla', 43)],
        ]))->assertUnprocessable()
            ->assertJsonValidationErrors(['accommodations']);

        $this->assertDatabaseCount('hotels', 0);
    }

    /** Exige al menos una acomodación al crear el hotel. */
    public function test_a_hotel_requires_at_least_one_accommodation(): void
    {
        $this->postJson('/api/hotels', $this->payload([
            'accommodations' => [],
        ]))->assertUnprocessable()
            ->assertJsonValidationErrors(['accommodations']);
    }

    /** Impide repetir la misma pareja de tipo y acomodación en un hotel. */
    public function test_room_type_and_accommodation_cannot_repeat_in_the_same_hotel(): void
    {
        $this->postJson('/api/hotels', $this->payload([
            'room_count' => 20,
            'accommodations' => [
                $this->row('Estándar', 'Sencilla', 5),
                $this->row('Estándar', 'Sencilla', 5),
            ],
        ]))->assertUnprocessable()
            ->assertJsonValidationErrors(['accommodations.1.room_type_id']);
    }

    /** Rechaza una acomodación que no corresponde al tipo, como Junior sencilla. */
    public function test_it_rejects_an_accommodation_that_does_not_match_the_room_type(): void
    {
        $this->postJson('/api/hotels', $this->payload([
            'accommodations' => [$this->row('Junior', 'Sencilla', 4)],
        ]))->assertUnprocessable()
            ->assertJsonValidationErrors(['accommodations.0.accommodation_id']);
    }

    /** Rechaza el NIT 12345678-9 porque su dígito de verificación no es válido. */
    public function test_it_rejects_an_invalid_check_digit(): void
    {
        $this->postJson('/api/hotels', $this->payload([
            'nit' => '12345678-9',
        ]))->assertUnprocessable()
            ->assertJsonValidationErrors(['nit'])
            ->assertJsonPath('errors.nit.0', 'El NIT debe incluir un dígito de verificación válido.');
    }

    /** La cantidad de una acomodación debe ser un entero mayor o igual a 1. */
    public function test_quantity_must_be_a_positive_integer(): void
    {
        $this->postJson('/api/hotels', $this->payload([
            'accommodations' => [$this->row('Estándar', 'Sencilla', 0)],
        ]))->assertUnprocessable()
            ->assertJsonValidationErrors(['accommodations.0.quantity']);
    }

    /** Nombre, NIT y dirección son únicos, y las mayúsculas o los espacios internos sí cuentan. */
    public function test_name_nit_and_address_are_unique_without_ignoring_case_or_inner_spaces(): void
    {
        $this->postJson('/api/hotels', $this->payload())->assertCreated();

        $this->postJson('/api/hotels', $this->payload([
            'name' => '  DECAMERON CARTAGENA  ',
            'address' => 'OTRA DIRECCION',
            'nit' => $this->nit('900000001'),
        ]))->assertUnprocessable()
            ->assertJsonValidationErrors(['name']);

        $this->postJson('/api/hotels', $this->payload([
            'name' => 'decameron cartagena',
            'address' => 'CALLE 23 58-25',
            'nit' => $this->nit('900000002'),
        ]))->assertUnprocessable()
            ->assertJsonValidationErrors(['address']);

        $this->postJson('/api/hotels', $this->payload([
            'name' => 'DECAMERON BARU',
            'address' => 'CALLE 1',
            'nit' => '12345678-8',
        ]))->assertUnprocessable()
            ->assertJsonValidationErrors(['nit']);

        $this->postJson('/api/hotels', $this->payload([
            'name' => 'decameron cartagena',
            'address' => 'CALLE2358-25',
            'nit' => $this->nit('900000003'),
        ]))->assertCreated();
    }

    /** Al editar, reemplaza las acomodaciones anteriores por las nuevas. */
    public function test_it_updates_the_hotel_and_replaces_its_accommodations(): void
    {
        $hotelId = $this->postJson('/api/hotels', $this->payload())->json('data.id');

        $response = $this->putJson("/api/hotels/{$hotelId}", $this->payload([
            'city' => 'BARU',
            'room_count' => 30,
            'accommodations' => [$this->row('Suite', 'Doble', 8)],
        ]));

        $response->assertOk()
            ->assertJsonPath('data.city', 'BARU')
            ->assertJsonPath('data.configured_rooms', 8)
            ->assertJsonPath('data.accommodations.0.room_type.name', 'Suite')
            ->assertJsonCount(1, 'data.accommodations');
    }

    /** Busca por nombre o NIT y parte el listado en páginas de 10. */
    public function test_it_lists_hotels_with_search_and_pagination(): void
    {
        $this->postJson('/api/hotels', $this->payload())->assertCreated();
        $this->postJson('/api/hotels', $this->payload([
            'name' => 'DECAMERON BARU',
            'address' => 'ISLA BARU',
            'nit' => $this->nit('800111222'),
        ]))->assertCreated();

        $this->getJson('/api/hotels?search=baru')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', 'DECAMERON BARU');

        $this->getJson('/api/hotels?search=12345678-8')
            ->assertOk()
            ->assertJsonPath('data.0.nit', '12345678-8');

        for ($index = 1; $index <= 9; $index++) {
            $this->postJson('/api/hotels', $this->payload([
                'name' => sprintf('HOTEL %02d', $index),
                'address' => sprintf('DIRECCION %02d', $index),
                'nit' => $this->nit((string) (700000000 + $index)),
            ]))->assertCreated();
        }

        $this->getJson('/api/hotels?per_page=10')
            ->assertOk()
            ->assertJsonPath('meta.total', 11)
            ->assertJsonPath('meta.last_page', 2)
            ->assertJsonCount(10, 'data');
    }

    /** Muestra un hotel y, al eliminarlo, borra también sus acomodaciones. */
    public function test_it_shows_and_deletes_a_hotel_with_its_accommodations(): void
    {
        $hotelId = $this->postJson('/api/hotels', $this->payload())->json('data.id');

        $this->getJson("/api/hotels/{$hotelId}")
            ->assertOk()
            ->assertJsonPath('data.address', 'CALLE 23 58-25')
            ->assertJsonPath('data.accommodations.0.quantity', 25);

        $this->deleteJson("/api/hotels/{$hotelId}")->assertNoContent();
        $this->getJson("/api/hotels/{$hotelId}")->assertNotFound();
        $this->assertDatabaseCount('hotels', 0);
        $this->assertDatabaseCount('hotel_accommodations', 0);
    }

    /**
     * Arma un hotel válido. Lo que se pase en $overrides reemplaza esos datos.
     *
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function payload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'DECAMERON CARTAGENA',
            'address' => 'CALLE 23 58-25',
            'city' => 'CARTAGENA',
            'nit' => '12345678-8',
            'room_count' => 42,
            'accommodations' => [
                $this->row('Estándar', 'Sencilla', 25),
            ],
        ], $overrides);
    }

    /**
     * Arma una fila de acomodación buscando los ids por nombre.
     *
     * @return array{room_type_id: int, accommodation_id: int, quantity: int}
     */
    private function row(string $roomType, string $accommodation, int $quantity): array
    {
        return [
            'room_type_id' => RoomType::query()->where('name', $roomType)->value('id'),
            'accommodation_id' => Accommodation::query()->where('name', $accommodation)->value('id'),
            'quantity' => $quantity,
        ];
    }

    /** Calcula un NIT válido a partir del número, sin el dígito. */
    private function nit(string $body): string
    {
        return $body.'-'.(new NitVerifier)->checkDigit($body);
    }
}
