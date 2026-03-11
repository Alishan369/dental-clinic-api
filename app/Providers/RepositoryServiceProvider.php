<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind(
           \App\Repositories\Contracts\DoctorRepositoryInterface::class,
           \App\Repositories\Eloquent\DoctorRepository::class
        );

        $this->app->bind(
            \App\Repositories\Contracts\PatientRepositoryInterface::class,
            \App\Repositories\Eloquent\PatientRepository::class
        );

        $this->app->bind(
            \App\Repositories\Contracts\AppointmentRepositoryInterface::class,
            \App\Repositories\Eloquent\AppointmentRepository::class
        );

        $this->app->bind(
            \App\Repositories\Contracts\DiseaseRepositoryInterface::class,
            \App\Repositories\Eloquent\DiseaseRepository::class
        );

        $this->app->bind(
            \App\Repositories\Contracts\DashboardRepositoryInterface::class,
            \App\Repositories\Eloquent\DashboardRepository::class
        );
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
