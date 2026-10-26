<?php

namespace App\Http\Controllers\Api;

use App\Domain\Catalogs\RoomCatalog;
use App\Http\Controllers\Controller;
use App\Http\Resources\RoomTypeResource;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/** Entrega el catálogo fijo de tipos de habitación. */
class RoomTypeController extends Controller
{
    public function __construct(private RoomCatalog $catalog) {}

    /** Lista cada tipo con las acomodaciones que tiene permitidas. */
    public function index(): AnonymousResourceCollection
    {
        return RoomTypeResource::collection($this->catalog->types());
    }
}
