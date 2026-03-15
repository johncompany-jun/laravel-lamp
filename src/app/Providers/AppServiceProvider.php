<?php

namespace App\Providers;

use App\Domain\Event\Repositories\EventApplicationRepositoryInterface;
use App\Domain\Event\Repositories\EventAssignmentRepositoryInterface;
use App\Domain\Event\Repositories\EventRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\EloquentEventApplicationRepository;
use App\Infrastructure\Persistence\Eloquent\EloquentEventAssignmentRepository;
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
        $this->app->bind(EventRepositoryInterface::class, EloquentEventRepository::class);
        $this->app->bind(EventApplicationRepositoryInterface::class, EloquentEventApplicationRepository::class);
        $this->app->bind(EventAssignmentRepositoryInterface::class, EloquentEventAssignmentRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
