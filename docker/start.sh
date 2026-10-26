#!/bin/sh
# Arranca la API: descubre paquetes, migra y solo carga el hotel de ejemplo si la base está vacía.
set -eu

php artisan package:discover --ansi
php artisan migrate --force

hotel_count="$(php -r 'require "vendor/autoload.php"; $app = require "bootstrap/app.php"; $kernel = $app->make(Illuminate\Contracts\Console\Kernel::class); $kernel->bootstrap(); echo App\Models\Hotel::query()->count();')"

if [ "$hotel_count" = "0" ]; then
  php artisan db:seed --force
fi

exec php artisan serve --host=0.0.0.0 --port="${PORT:-8000}"
