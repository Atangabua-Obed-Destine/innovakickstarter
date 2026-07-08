<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
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
        // Custom route model binding for InterviewSession
        Route::model('interview', \App\Models\InterviewSession::class);

        // Register policies
        Gate::policy(\App\Models\InterviewSession::class, \App\Policies\InterviewSessionPolicy::class);
    }
}
