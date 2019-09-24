<?php
namespace NunoLopes\LaravelContactsAPI\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use NunoLopes\DomainContacts\Exceptions\Repositories\Users\UserNotFoundException;
use NunoLopes\DomainContacts\Exceptions\Services\Authentication\PasswordMismatchException;
use NunoLopes\DomainContacts\Requests\Authentication\LoginUserRequest;
use NunoLopes\DomainContacts\Requests\Authentication\RegisterUserRequest;
use NunoLopes\DomainContacts\Services\AccessTokenService;
use NunoLopes\DomainContacts\Services\AuthenticationService;

/**
 * Class AuthenticationController.
 *
 * This class will be responsible for Authentication actions.
 *
 * @todo Handle exceptions with try/catch and right status code.
 *
 * @package NunoLopes\LaravelContactsAPI
 */
class AuthenticationController
{
    /**
     * @var AuthenticationService - Authentication Service instance.
     */
    private $authenticationService = null;

    /**
     * @var AccessTokenService - AccessToken Service instance.
     */
    private $accessTokenService = null;

    /**
     * AuthenticationController constructor.
     *
     * @param AuthenticationService $authenticationService - Authentication Service instance.
     * @param AccessTokenService    $accessTokenService    - Access Token Service instance.
     */
    public function __construct(
        AuthenticationService $authenticationService,
        AccessTokenService $accessTokenService
    ) {
        $this->authenticationService = $authenticationService;
        $this->accessTokenService    = $accessTokenService;
    }

    /**
     * Registers a new user and returns a created Authentication token.
     *
     * @param RegisterUserRequest $request - User Registration Request.
     *
     * @return Response
     */
    public function register (RegisterUserRequest $request): Response
    {
        $token = $this->authenticationService->register($request->validated());

        return response($token, 200);
    }

    /**
     * Logins an existent user.
     *
     * Returns a created access token.
     *
     * @param LoginUserRequest $request - LoginUser's Request.
     *
     * @throws PasswordMismatchException - If the password was incorrect.
     * @throws UserNotFoundException     - If the user was not found.
     *
     * @return Response
     */
    public function login(LoginUserRequest $request): Response
    {
        $token = $this->authenticationService->login($request->validated());

        return response($token, 200);
    }

    /**
     * Returns the loggedin user.
     *
     * @param Request $request - User's Request.
     *
     * @return Response
     */
    public function user(Request $request): Response
    {
        $user = $this->accessTokenService->getTokenUser($request->bearerToken());

        return response()
            ->view('laravel-contacts-api::authentication.user', [ 'user' => $user ], 200);
    }

    /**
     * Logs out an existent user by revoking the token that is being used.
     *
     * @param Request $request - LogoutUser's Request.
     *
     * @return Response
     */
    public function logout(Request $request): Response
    {
        $this->accessTokenService->revokeToken($request->bearerToken());

        return response(null, 204);
    }
}
