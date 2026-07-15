<?php

namespace App\Providers;

use App\Services\TmdbService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(TmdbService::class, fn () => new TmdbService(
            apiKey: config('services.tmdb.key'),
            baseUrl: config('services.tmdb.base_url'),
            imageBaseUrl: config('services.tmdb.image_base_url'),
        ));
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
