<?php
namespace NunoLopes\LaravelContactsAPI;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider as BaseServiceProvider;
use NunoLopes\DomainContacts\Contracts\Repositories\Database\AccessTokenRepository;
use NunoLopes\DomainContacts\Contracts\Repositories\Database\ContactsRepository;
use NunoLopes\DomainContacts\Contracts\Repositories\Database\UsersRepository;
use NunoLopes\DomainContacts\Contracts\Services\AuthenticationTokenService;
use NunoLopes\DomainContacts\Contracts\Utilities\Authentication as AuthenticationContract;
use NunoLopes\DomainContacts\Contracts\Utilities\RsaSignature;
use NunoLopes\DomainContacts\Datatypes\AsymmetricCryptography;
use NunoLopes\DomainContacts\Exceptions\BaseException;
use NunoLopes\DomainContacts\Exceptions\Services\Authentication\TokenRevokedException;
use NunoLopes\DomainContacts\Factories\Repositories\ConfigurationRepositoryFactory;
use NunoLopes\DomainContacts\Factories\Services\AccessTokenServiceFactory;
use NunoLopes\DomainContacts\Repositories\Database\Eloquent\EloquentAccessTokenRepository;
use NunoLopes\DomainContacts\Repositories\Database\Eloquent\EloquentContactsRepository;
use NunoLopes\DomainContacts\Repositories\Database\Eloquent\EloquentUsersRepository;
use NunoLopes\DomainContacts\Services\AuthenticationToken\JwtAuthenticationTokenService;
use NunoLopes\DomainContacts\Utilities\Authentication;
use NunoLopes\DomainContacts\Utilities\Signatures\Sha256RsaSignature;

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
     * All of the container singletons that should be registered.
     *
     * @var array
     */
    public $singletons = [
        ContactsRepository::class         => EloquentContactsRepository::class,
        UsersRepository::class            => EloquentUsersRepository::class,
        AccessTokenRepository::class      => EloquentAccessTokenRepository::class,
        AuthenticationTokenService::class => JwtAuthenticationTokenService::class,
        RsaSignature::class               => Sha256RsaSignature::class
    ];

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
        $this->handleInstances();
    }

    /**
     * Register instances for the constructors.
     *
     * @return void
     */
    private function handleInstances(): void
    {
        $this->app->instance(
            AsymmetricCryptography::class,
            ConfigurationRepositoryFactory::get()->getRSA()
        );

        $request = Request::createFromGlobals();

        $accessToken = null;
        try {
            if ($request->bearerToken() !== null) {
                $accessToken = AccessTokenServiceFactory::get()->getAccessToken($request->bearerToken());

                if ($accessToken->revoked()) {
                    throw new TokenRevokedException();
                }
            }
        } catch (BaseException $e) {
            abort($e->getCode(), $e->getMessage());
        }

        $this->app->instance(
            AuthenticationContract::class,
            new Authentication($accessToken)
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
