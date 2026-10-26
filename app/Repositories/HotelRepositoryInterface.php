<?php

namespace App\Repositories;

use App\Models\Hotel;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

/** Contrato de acceso a los hoteles, independiente de Eloquent. */
interface HotelRepositoryInterface
{
    /** Devuelve una página de hoteles, filtrada por nombre o NIT si hay búsqueda. */
    public function paginate(?string $search, int $perPage): LengthAwarePaginator;

    /** Carga el detalle del hotel y la suma de habitaciones configuradas. */
    public function loadDetail(Hotel $hotel): Hotel;

    /**
     * Guarda el hotel y sus acomodaciones.
     *
     * @param  array<string, mixed>  $attributes
     * @param  list<array<string, mixed>>  $accommodations
     */
    public function create(array $attributes, array $accommodations): Hotel;

    /**
     * Actualiza el hotel y vuelve a guardar sus acomodaciones.
     *
     * @param  array<string, mixed>  $attributes
     * @param  list<array<string, mixed>>  $accommodations
     */
    public function update(Hotel $hotel, array $attributes, array $accommodations): Hotel;

    /** Borra el hotel. */
    public function delete(Hotel $hotel): void;
}
