<?php

namespace App\Providers;

use App\Repositories\EloquentHotelRepository;
use App\Repositories\HotelRepositoryInterface;
use Illuminate\Support\ServiceProvider;

/** Registra los servicios que la aplicación necesita al iniciar. */
class AppServiceProvider extends ServiceProvider
{
    /**
     * Registra el repositorio de hoteles.
     */
    public function register(): void
    {
        $this->app->bind(HotelRepositoryInterface::class, EloquentHotelRepository::class);
    }

    /**
     * Se ejecuta cuando la aplicación termina de arrancar.
     */
    public function boot(): void
    {
        //
    }
}
