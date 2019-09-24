<?php
namespace NunoLopes\LaravelContactsAPI;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider as BaseServiceProvider;
use NunoLopes\DomainContacts\Contracts\Database\AccessTokenRepository;
use NunoLopes\DomainContacts\Contracts\Database\ContactsRepository;
use NunoLopes\DomainContacts\Contracts\Database\UsersRepository;
use NunoLopes\DomainContacts\Contracts\Services\AuthenticationTokenService;
use NunoLopes\DomainContacts\Contracts\Utilities\AsymmetricCryptography;
use NunoLopes\DomainContacts\Contracts\Utilities\Authentication;
use NunoLopes\DomainContacts\Repositories\Database\Eloquent\EloquentAccessTokenRepository;
use NunoLopes\DomainContacts\Repositories\Database\Eloquent\EloquentContactsRepository;
use NunoLopes\DomainContacts\Repositories\Database\Eloquent\EloquentUsersRepository;
use NunoLopes\DomainContacts\Services\AuthenticationToken\JwtAuthenticationTokenService;
use NunoLopes\LaravelContactsAPI\Utilities\LaravelAsymmetricCryptography;
use NunoLopes\LaravelContactsAPI\Utilities\LaravelAuthentication;

/**
 * Class AuthenticationController.
 *
 * Service providers are the central place of all Laravel application bootstrapping.
 *
 * @package NunoLopes\LaravelContactsAPI
 */
class ServiceProvider extends BaseServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot(): void
    {
        // Load dependencies
        require_once __DIR__ . '/../vendor/autoload.php';

        $this->handleRoutes();
        $this->handleViews();
    }

    /**
     * Register any application services.
     *
     * This function will bind Contracts with eloquent classes so we can have
     * dependency injections without using Factories for example.
     *
     * @return void
     */
    public function register(): void
    {
        $this->app->bind(
            ContactsRepository::class,
            EloquentContactsRepository::class
        );
        $this->app->bind(
            UsersRepository::class,
            EloquentUsersRepository::class
        );
        $this->app->bind(
            Authentication::class,
            LaravelAuthentication::class
        );
        $this->app->bind(
            AccessTokenRepository::class,
            EloquentAccessTokenRepository::class
        );
        $this->app->bind(
            AuthenticationTokenService::class,
            JwtAuthenticationTokenService::class
        );
        $this->app->bind(
            AsymmetricCryptography::class,
            LaravelAsymmetricCryptography::class
        );
    }

    /**
     * Handles routes.
     *
     * @return void
     */
    private function handleRoutes(): void
    {
        Route::prefix('api')
            ->group(
                __DIR__ . '/../routes/api.php'
            );

        $this->loadRoutesFrom(__DIR__ . '/../routes/routes.php');
    }

    /**
     * Handles Views.
     *
     * @return void
     */
    private function handleViews(): void
    {
        $this->loadViewsFrom(__DIR__ . '/../views', 'laravel-contacts-api');
    }
}
