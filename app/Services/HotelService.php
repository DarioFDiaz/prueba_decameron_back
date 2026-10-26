<?php

namespace App\Services;

use App\Models\Hotel;
use App\Repositories\HotelRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

/** Coordina las operaciones de hoteles entre la API y la base de datos. */
class HotelService
{
    public function __construct(private HotelRepositoryInterface $hotels) {}

    /** Busca hoteles y limita el tamaño de cada página. */
    public function list(?string $search, int $perPage): LengthAwarePaginator
    {
        $perPage = min(max($perPage, 1), 50);

        return $this->hotels->paginate($search, $perPage);
    }

    /**
     * Separa los datos del hotel de sus acomodaciones y lo crea.
     *
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Hotel
    {
        $accommodations = $data['accommodations'];
        unset($data['accommodations']);

        return $this->hotels->create($data, $accommodations);
    }

    /** Carga el hotel con sus acomodaciones para mostrarlo. */
    public function show(Hotel $hotel): Hotel
    {
        return $this->hotels->loadDetail($hotel);
    }

    /**
     * Reemplaza los datos y las acomodaciones del hotel.
     *
     * @param  array<string, mixed>  $data
     */
    public function update(Hotel $hotel, array $data): Hotel
    {
        $accommodations = $data['accommodations'];
        unset($data['accommodations']);

        return $this->hotels->update($hotel, $data, $accommodations);
    }

    /** Elimina el hotel. */
    public function delete(Hotel $hotel): void
    {
        $this->hotels->delete($hotel);
    }
}
