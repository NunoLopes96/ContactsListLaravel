<?php
namespace NunoLopes\LaravelContactsAPI\Controllers;

use Illuminate\Http\Response;
use NunoLopes\DomainContacts\Exceptions\BaseException;
use NunoLopes\DomainContacts\Exceptions\Repositories\Users\UserNotFoundException;
use NunoLopes\DomainContacts\Exceptions\Services\Authentication\PasswordMismatchException;
use NunoLopes\DomainContacts\Requests\Authentication\LoginUserRequest;
use NunoLopes\DomainContacts\Requests\Authentication\RegisterUserRequest;
use NunoLopes\DomainContacts\Services\AuthenticationService;

/**
 * Class AuthenticationController.
 *
 * This class will be responsible for Authentication actions.
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
     * AuthenticationController constructor.
     *
     * @param AuthenticationService $authenticationService - Authentication Service instance.
     */
    public function __construct(AuthenticationService $authenticationService)
    {
        $this->authenticationService = $authenticationService;
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
        if ($request->fails()) {
            return response([ 'errors' => $request->errors() ], 422);
        }

        try {
            $token = $this->authenticationService->register(
                $request->name(),
                $request->email(),
                $request->password()
            );
        } catch (BaseException $e) {
            abort($e->getCode(), $e->getMessage());
        }

        return response([ 'data' => $token ], 200);
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
        if ($request->fails()) {
            return response([ 'errors' => $request->errors() ], 422);
        }

        try {
            $token = $this->authenticationService->login(
                $request->name(),
                $request->password()
            );
        } catch (BaseException $e) {
            abort($e->getCode(), $e->getMessage());
        }

        return response([ 'data' => $token ], 200);
    }

    /**
     * Returns the loggedin user.
     *
     * @return Response
     */
    public function user(): Response
    {
        try {
            $user = $this->authenticationService->user();
        } catch (BaseException $e) {
            abort($e->getCode(), $e->getMessage());
        }

        return response()
            ->view('laravel-contacts-api::authentication.user', [ 'user' => $user ], 200);
    }

    /**
     * Logs out an existent user by revoking the token that is being used.
     *
     * @return Response
     */
    public function logout(): Response
    {
        try {
            $this->authenticationService->logout();
        } catch (BaseException $e) {
            abort($e->getCode(), $e->getMessage());
        }

        return response(null, 204);
    }
}
