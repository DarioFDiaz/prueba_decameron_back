<?php

use App\Http\Controllers\Api\HotelController;
use App\Http\Controllers\Api\RoomTypeController;
use Illuminate\Support\Facades\Route;

/** Rutas REST consumidas por el frontend. */
Route::get('/room-types', [RoomTypeController::class, 'index']);
Route::apiResource('hotels', HotelController::class);
