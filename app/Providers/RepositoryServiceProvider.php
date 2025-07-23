<?php

namespace App\Providers;

use App\Repositories\CalendarCategory\Concretes\CalendarCategoryRepository;
use App\Repositories\CalendarCategory\Contracts\CalendarCategoryRepositoryInterface;
use App\Repositories\CalendarEvent\Concretes\CalendarEventRepository;
use App\Repositories\CalendarEvent\Contracts\CalendarEventRepositoryInterface;
use App\Repositories\User\Concretes\UserRepository;
use App\Repositories\User\Contracts\UserRepositoryInterface;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Register repository bindings here
        $this->app->bind(UserRepositoryInterface::class, UserRepository::class);

        $this->app->bind(CalendarEventRepositoryInterface::class, CalendarEventRepository::class);

        $this->app->bind(CalendarCategoryRepositoryInterface::class, CalendarCategoryRepository::class);

    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
