<?php

namespace App\Repositories;

use App\Models\Hotel;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

/** Lee y guarda hoteles usando Eloquent. */
class EloquentHotelRepository implements HotelRepositoryInterface
{
    /** Lista los hoteles y, si llega un texto, lo busca en el nombre o el NIT. */
    public function paginate(?string $search, int $perPage): LengthAwarePaginator
    {
        return Hotel::query()
            ->withSum('accommodations as configured_rooms', 'quantity')
            ->when($search, function ($query, string $search) {
                $term = $this->likeTerm($search);

                $query->where(function ($query) use ($term) {
                    $query->where('name', 'ilike', $term)
                        ->orWhere('nit', 'ilike', $term);
                });
            })
            ->orderBy('name')
            ->paginate($perPage);
    }

    /** Trae las acomodaciones del hotel y cuántas habitaciones suman. */
    public function loadDetail(Hotel $hotel): Hotel
    {
        return $hotel
            ->load($this->relations())
            ->loadSum('accommodations as configured_rooms', 'quantity');
    }

    /** Crea el hotel y sus acomodaciones en una sola transacción. */
    public function create(array $attributes, array $accommodations): Hotel
    {
        return DB::transaction(function () use ($attributes, $accommodations) {
            $hotel = Hotel::query()->create($attributes);
            $hotel->accommodations()->createMany($accommodations);

            return $this->loadDetail($hotel);
        });
    }

    /** Actualiza el hotel y sustituye todas sus acomodaciones. */
    public function update(Hotel $hotel, array $attributes, array $accommodations): Hotel
    {
        return DB::transaction(function () use ($hotel, $attributes, $accommodations) {
            $hotel->update($attributes);
            $hotel->accommodations()->delete();
            $hotel->accommodations()->createMany($accommodations);

            return $this->loadDetail($hotel);
        });
    }

    /** Elimina el hotel. Las acomodaciones se borran en cascada. */
    public function delete(Hotel $hotel): void
    {
        $hotel->delete();
    }

    /**
     * Relaciones que necesita el detalle del hotel.
     *
     * @return list<string>
     */
    private function relations(): array
    {
        return ['accommodations.roomType', 'accommodations.accommodation'];
    }

    /** Prepara el texto para una búsqueda que no se confunda con comodines. */
    private function likeTerm(string $search): string
    {
        return '%'.addcslashes($search, '\\%_').'%';
    }
}
