<?php

namespace App\Providers;

use App\Services\Integrations\IntegrationSettings;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(IntegrationSettings::class);
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

        $this->applyIntegrationSettings();
    }

    /**
     * Keys saved on the admin Integrations page override .env. Queue workers
     * re-read them before each job so a new key works without a restart.
     */
    protected function applyIntegrationSettings(): void
    {
        // config:cache / optimize must cache the .env values, never the
        // dashboard ones (they would be written to disk decrypted).
        $command = $_SERVER['argv'][1] ?? null;
        if ($this->app->runningInConsole() && in_array($command, ['config:cache', 'optimize'], true)) {
            return;
        }

        $settings = $this->app->make(IntegrationSettings::class);
        $settings->apply();

        Queue::before(fn () => $settings->apply());
    }
}
