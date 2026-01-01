<?php

namespace App\Providers;

use App\Domain\Event\Repositories\EventRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\EloquentEventRepository;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Repository bindings
        $this->app->bind(
            EventRepositoryInterface::class,
            EloquentEventRepository::class
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
