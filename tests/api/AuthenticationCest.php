<?php

use NunoLopes\DomainContacts\Factories\Database\Eloquent\CapsuleFactory;

/**
 * Class AuthenticationCest.
 *
 * This class will test the API related with the authentications.
 *
 * @package NunoLopes\LaravelContactsAPI
 */
class AuthenticationCest
{
    /**
     * @var string $username - Random username generated.
     */
    private $username = null;

    /**
     * @var string $password - Random password generated.
     */
    private $password = null;

    /**
     * @var string $email - Random email generated.
     */
    private $email = null;

    /**
     * @var string $token - Authentication token after a successful login.
     */
    private $token = null;

    /**
     * Inject dependencies into the class.
     *
     * @return void
     */
    public function _inject(): void
    {
        // Require domain's namespaces
        // @todo Domain's namespace not being automatically auto-loaded.
        require_once __DIR__ . '/../../vendor/nuno/domain-contacts/vendor/autoload.php';

        $faker = \Faker\Factory::create();

        $this->username = $faker->userName;
        $this->password = $faker->password;
        $this->email    = $faker->email;
    }

    /**
     * Try to register successfully an user that doesn't exists.
     *
     * @param ApiTester $I - The API Tester.
     *
     * @return void
     */
    public function registerUserViaAPI(\ApiTester $I): void
    {
        // Try to register an user that doesn't exists.
        $I->sendPOST('api/user/register', [
            'name'     => $this->username,
            'password' => $this->password,
            'email'    => $this->email,
        ]);

        // Check if the response code is empty.
        $I->seeResponseCodeIs(\Codeception\Util\HttpCode::NO_CONTENT);
    }

    /**
     * Try to register with invalid data.
     *
     * @param ApiTester $I - The API Tester.
     *
     * @return void
     */
    public function registerUserViaAPIWithInvalidData(\ApiTester $I): void
    {
        // Send a post with password missing.
        $I->sendPOST('api/user/register', [
            'name'  => $this->username,
            'email' => $this->email,
        ]);

        // Check the response code is correct.
        $I->seeResponseCodeIs(\Codeception\Util\HttpCode::UNPROCESSABLE_ENTITY);

        // Check that the response has the errors information.
        $I->assertArrayHasKey(
            'errors',
            \json_decode($I->grabResponse(), true)
        );
    }

    /**
     * Try to register an user that already exists.
     *
     * @param ApiTester $I - The API Tester.
     *
     * @return void
     */
    public function registerUserViaAPIWithConflictingData(\ApiTester $I): void
    {
        // Try to register the same name twice.
        $I->sendPOST('api/user/register', [
            'name'     => $this->username,
            'password' => $this->password,
            'email'    => $this->email,
        ]);

        // Check the response code is correct.
        $I->seeResponseCodeIs(\Codeception\Util\HttpCode::CONFLICT);
    }

    /**
     * Try to login the user that was registered previously.
     *
     * @param ApiTester $I - The API Tester.
     *
     * @return void
     */
    public function loginUserViaAPI(\ApiTester $I): void
    {
        // Send post with the registered name and password before.
        $I->sendPOST('api/user/login', [
            'name'     => $this->username,
            'password' => $this->password,
        ]);

        // Check the response code is correct.
        $I->seeResponseCodeIs(\Codeception\Util\HttpCode::OK); // 200

        // Check a token was returned.
        $I->seeResponseIsJson();
        $I->assertArrayHasKey(
            'data',
            $response = \json_decode($I->grabResponse(), true)
        );

        // Save the authentication token returned.
        $this->token = $response['data'];
    }

    /**
     * Try to login with invalid data.
     *
     * @param ApiTester $I - The API Tester.
     *
     * @return void
     */
    public function loginUserViaAPIWithInvalidData(\ApiTester $I): void
    {
        // Send a post with password missing.
        $I->sendPOST('api/user/login', [
            'name' => $this->username,
        ]);

        // Check the response code is correct.
        $I->seeResponseCodeIs(\Codeception\Util\HttpCode::UNPROCESSABLE_ENTITY);

        // Check that the response has the errors information.
        $I->seeResponseIsJson();
        $I->assertArrayHasKey(
            'errors',
            \json_decode($I->grabResponse(), true)
        );
    }

    /**
     * Try to login an user with wrong credentials.
     *
     * @param ApiTester $I - The API Tester.
     *
     * @return void
     */
    public function loginUserViaAPIWithWrongCredentials(\ApiTester $I): void
    {
        // Set header to return a json.
        $I->haveHttpHeader('Accept', 'application/json');

        // Try to register the same name twice.
        $I->sendPOST('api/user/login', [
            'name'     => $this->username,
            'password' => $this->password . '.',
        ]);

        // Check the response code is correct.
        $I->seeResponseCodeIs(\Codeception\Util\HttpCode::UNAUTHORIZED);
    }

    /**
     * Try to return current user information when sending Authorization Token.
     *
     * @param ApiTester $I - The API Tester.
     *
     * @return void
     */
    public function checkUserViaAPI(\ApiTester $I): void
    {
        // Set headers for the GET request.
        $I->haveHttpHeader('Accept', 'application/json');
        $I->haveHttpHeader('Authorization', $this->token);

        // Return information of the user with the Authorization Header.
        $I->sendGET('api/user');

        // Check the response code is correct.
        $I->seeResponseCodeIs(\Codeception\Util\HttpCode::OK);

        // Check the response has returned data.
        $I->seeResponseIsJson();
        $I->assertArrayHasKey(
            'data',
            $response = \json_decode($I->grabResponse(), true)
        );
    }

    /**
     * Try to return current user information with invalid authorization token.
     *
     * @param ApiTester $I - The API Tester.
     *
     * @return void
     */
    public function checkUserViaAPIWithWrongToken(\ApiTester $I): void
    {
        // Set headers for the GET request.
        $I->haveHttpHeader('Accept', 'application/json');
        $I->haveHttpHeader('Authorization', $this->token . '.');

        // Return information of the user with the Authorization Header.
        $I->sendGET('api/user');

        // Check the response code is correct.
        $I->seeResponseCodeIs(\Codeception\Util\HttpCode::UNAUTHORIZED);

        // Check the response has returned data.
        $I->seeResponseIsJson();
        $I->assertArrayHasKey(
            'message',
            $response = \json_decode($I->grabResponse(), true)
        );
    }

    /**
     * Try to logout logged-in user.
     *
     * @param ApiTester $I - The API Tester.
     *
     * @return void
     */
    public function logoutUserViaAPI(\ApiTester $I): void
    {
        // Set authorization token in header.
        $I->haveHttpHeader('Authorization', $this->token);

        // Send post to logout the user and invalidate the token.
        $I->sendPOST('api/user/logout');

        // Check the response code is correct.
        $I->seeResponseCodeIs(\Codeception\Util\HttpCode::NO_CONTENT);
    }

    /**
     * Try to logout logged-in user that is not logged-in.
     *
     * @param ApiTester $I - The API Tester.
     *
     * @return void
     */
    public function logoutUserViaAPIUnauthorized(\ApiTester $I): void
    {
        // Set headers for the GET request.
        $I->haveHttpHeader('Accept', 'application/json');

        // Send post to logout the user and invalidate the token.
        $I->sendPOST('api/user/logout');

        // Check the response code is correct.
        $I->seeResponseCodeIs(\Codeception\Util\HttpCode::UNAUTHORIZED);
    }

    /**
     * Execute code after all tests have run.
     *
     * @return void
     */
    public function tearDownAfterClass(): void
    {
        // Boot domain's eloquent.
        CapsuleFactory::get();

        // Delete the registered user.
        \NunoLopes\DomainContacts\Eloquent\User::query()->where([
            'name'  => $this->username,
            'email' => $this->email,
        ])->delete();
    }
}
