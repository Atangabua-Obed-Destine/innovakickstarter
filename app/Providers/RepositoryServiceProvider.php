<?php

namespace App\Providers;

use App\Repositories\ActivityRepository;
use App\Repositories\Contracts\ActivityRepositoryInterface;
use App\Repositories\Contracts\CurriculumRepositoryInterface;
use App\Repositories\Contracts\FellowRepositoryInterface;
use App\Repositories\Contracts\InterviewRepositoryInterface;
use App\Repositories\Contracts\TrackRepositoryInterface;
use App\Repositories\CurriculumRepository;
use App\Repositories\FellowRepository;
use App\Repositories\InterviewRepository;
use App\Repositories\TrackRepository;
use Illuminate\Support\ServiceProvider;

/**
 * Repository Service Provider
 * 
 * Binds repository interfaces to their implementations.
 * Enables dependency injection of repositories throughout the application.
 * 
 * @author IKS Engineering Team
 * @version 1.0
 */
class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * All repository bindings.
     */
    protected array $repositories = [
        FellowRepositoryInterface::class => FellowRepository::class,
        TrackRepositoryInterface::class => TrackRepository::class,
        ActivityRepositoryInterface::class => ActivityRepository::class,
        InterviewRepositoryInterface::class => InterviewRepository::class,
        CurriculumRepositoryInterface::class => CurriculumRepository::class,
    ];

    /**
     * Register services.
     */
    public function register(): void
    {
        foreach ($this->repositories as $interface => $implementation) {
            $this->app->bind($interface, $implementation);
        }
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
