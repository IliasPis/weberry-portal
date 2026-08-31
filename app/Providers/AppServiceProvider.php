<?php

namespace App\Providers;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Vite::prefetch(concurrency: 3);

        // API Resources feed Inertia props here, not a JSON API for
        // external consumers - the {"data": [...]} envelope only adds a
        // layer every Vue prop type would otherwise have to account for.
        JsonResource::withoutWrapping();
    }
}
