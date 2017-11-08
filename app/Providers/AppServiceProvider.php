<?php

namespace Twinleaf\Providers;

use Twinleaf\Map;
use Twinleaf\MapArea;
use Twinleaf\Observers\MapObserver;
use Twinleaf\Observers\MapAreaObserver;

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Schema::defaultStringLength(191);

        Map::observe(MapObserver::class);
        MapArea::observe(MapAreaObserver::class);
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
