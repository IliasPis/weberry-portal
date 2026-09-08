<?php

namespace App\Providers;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\DB;
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

        // Case-insensitive LIKE search that works the same across every
        // connection the app runs on: Postgres needs the explicit "ilike"
        // operator, MySQL/MariaDB (production) is already
        // case-insensitive under its default collation so plain "like"
        // is correct there - a hardcoded "ilike" everywhere would silently
        // become case-sensitive-only, or error outright, off Postgres.
        Builder::macro('whereLike', function (string $column, string $value) {
            /** @var Builder $this */
            $operator = DB::connection($this->getModel()->getConnectionName())->getDriverName() === 'pgsql' ? 'ilike' : 'like';

            return $this->where($column, $operator, $value);
        });

        Builder::macro('orWhereLike', function (string $column, string $value) {
            /** @var Builder $this */
            $operator = DB::connection($this->getModel()->getConnectionName())->getDriverName() === 'pgsql' ? 'ilike' : 'like';

            return $this->orWhere($column, $operator, $value);
        });
    }
}
