<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreHotelRequest;
use App\Http\Requests\UpdateHotelRequest;
use App\Http\Resources\HotelResource;
use App\Models\Hotel;
use App\Services\HotelService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

/** Atiende el CRUD de hoteles por la API. */
class HotelController extends Controller
{
    public function __construct(private HotelService $hotels) {}

    /** Lista los hoteles y permite buscarlos por nombre o NIT. */
    public function index(Request $request): AnonymousResourceCollection
    {
        $search = trim((string) $request->query('search', ''));

        return HotelResource::collection(
            $this->hotels->list($search === '' ? null : $search, (int) $request->query('per_page', 10))
        );
    }

    /** Crea un hotel con sus acomodaciones. */
    public function store(StoreHotelRequest $request): JsonResponse
    {
        $hotel = $this->hotels->create($request->validated());

        return (new HotelResource($hotel))->response()->setStatusCode(201);
    }

    /** Devuelve el detalle de un hotel. */
    public function show(Hotel $hotel): HotelResource
    {
        return new HotelResource($this->hotels->show($hotel));
    }

    /** Actualiza los datos del hotel y reemplaza sus acomodaciones. */
    public function update(UpdateHotelRequest $request, Hotel $hotel): HotelResource
    {
        return new HotelResource($this->hotels->update($hotel, $request->validated()));
    }

    /** Elimina el hotel y, con él, sus acomodaciones. */
    public function destroy(Hotel $hotel): Response
    {
        $this->hotels->delete($hotel);

        return response()->noContent();
    }
}
