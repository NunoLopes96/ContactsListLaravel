<?php

use NunoLopes\DomainContacts\Eloquent\Contact as ContactModel;
use NunoLopes\DomainContacts\Eloquent\User as UserModel;
use NunoLopes\DomainContacts\Entities\User;
use NunoLopes\DomainContacts\Entities\Contact;
use NunoLopes\DomainContacts\Factories\Database\Eloquent\CapsuleFactory;
use NunoLopes\DomainContacts\Factories\Services\AccessTokenServiceFactory;

/**
 * Class ContactsCest.
 *
 * This class will test the API related with the operations on the contacts.
 *
 * @package NunoLopes\LaravelContactsAPI
 */
class ContactsCest
{
    /**
     * @var string $authToken - Authentication token of the initial user.
     */
    private $authToken = null;

    /**
     * @var User $user - Random User to be Logged-In.
     */
    private $user = null;

    /**
     * @var array $contact - Attributes of the created contact during the tests.
     */
    private $contact = null;

    /**
     * @var \Faker\Generator $faker - Random string generator.
     */
    private $faker = null;

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

        // Boot domain's eloquent.
        CapsuleFactory::get();

        $this->user      = new User(UserModel::query()->inRandomOrder()->first()->getAttributes());
        $this->authToken = AccessTokenServiceFactory::get()->createToken($this->user);
        $this->faker     = \Faker\Factory::create();
    }

    /**
     * Try to create successfully a new contact.
     *
     * @param \ApiTester $I - The API Tester.
     *
     * @return void
     */
    public function createContactViaAPI(\ApiTester $I): void
    {
        // Set the authorization header.
        $I->haveHttpHeader('Authorization', $this->authToken);

        // Create an user with valid data.
        $I->sendPOST('api/contacts', [
            'first_name' => $this->faker->firstName,
        ]);

        // Check the response code is correct.
        $I->seeResponseCodeIs(\Codeception\Util\HttpCode::OK);

        // Check the response is an array and contains data.
        $I->seeResponseIsJson();
        $I->assertArrayHasKey(
            'data',
            $response = \json_decode($I->grabResponse(), true)
        );

        $this->contact = $response['data']['contact'];
    }

    /**
     * Try to create a contact with invalid data.
     *
     * @param \ApiTester $I - The API Tester.
     *
     * @return void
     */
    public function createContactViaAPIWithInvalidData(\ApiTester $I): void
    {
        // Set the authorization header.
        $I->haveHttpHeader('Authorization', $this->authToken);

        // Create an user with invalid data (first_name) missing.
        $I->sendPOST('api/contacts', [
            'last_name' => $this->faker->firstName,
        ]);

        // Check the response code is correct.
        $I->seeResponseCodeIs(\Codeception\Util\HttpCode::UNPROCESSABLE_ENTITY);

        // Check the response is an array and contains data.
        $I->seeResponseIsJson();
        $I->assertArrayHasKey(
            'errors',
            \json_decode($I->grabResponse(), true)
        );
    }

    /**
     * Try to create a contact without an authentication.
     *
     * @param \ApiTester $I - The API Tester.
     *
     * @return void
     */
    public function createContactViaAPIWithoutAuthentication(\ApiTester $I): void
    {
        // Add header to accept json.
        $I->haveHttpHeader('Accept', 'application/json');

        // Create an user with valid data.
        $I->sendPOST('api/contacts', [
            'first_name' => $this->faker->firstName,
        ]);

        // Check the response code is correct.
        $I->seeResponseCodeIs(\Codeception\Util\HttpCode::UNAUTHORIZED);

        // Check the response is an array and contains data.
        $I->seeResponseIsJson();
        $I->assertArrayHasKey(
            'message',
            $response = \json_decode($I->grabResponse(), true)
        );
    }

    /**
     * Try to list all contacts of an authenticated user.
     *
     * @param \ApiTester $I - The API Tester.
     *
     * @return void
     */
    public function listAllContactsViaApiOfLoggedInUser(\ApiTester $I): void
    {
        // Set the authorization header.
        $I->haveHttpHeader('Authorization', $this->authToken);

        // Create an user with valid data.
        $I->sendGET('api/contacts');

        // Check the response code is correct.
        $I->seeResponseCodeIs(\Codeception\Util\HttpCode::OK);

        // Check the response is an array and contains data.
        $I->seeResponseIsJson();
        $I->assertArrayHasKey(
            'data',
            $response = \json_decode($I->grabResponse(), true)
        );
        $I->assertArrayHasKey(
            'contacts',
            $response['data']
        );
    }

    /**
     * Try to list all contacts without authentication.
     *
     * @param \ApiTester $I - The API Tester.
     *
     * @return void
     */
    public function listAllContactsViaApiWithoutAuthentication(\ApiTester $I): void
    {
        // Add header to accept json.
        $I->haveHttpHeader('Accept', 'application/json');

        // Create an user with valid data.
        $I->sendGET('api/contacts');

        // Check the response code is correct.
        $I->seeResponseCodeIs(\Codeception\Util\HttpCode::UNAUTHORIZED);

        // Check the response is an array and contains data.
        $I->seeResponseIsJson();
        $I->assertArrayHasKey(
            'message',
            \json_decode($I->grabResponse(), true)
        );
    }

    /**
     * Try to get a contact to edit without authentication.
     *
     * @param \ApiTester $I - The API Tester.
     *
     * @return void
     */
    public function getContactToEditViaApiOfLoggedInUser(\ApiTester $I): void
    {
        // Set the authorization header.
        $I->haveHttpHeader('Authorization', $this->authToken);

        // Create an user with valid data.
        $I->sendGET('api/contacts/' . $this->contact['id'] . '/edit');

        // Check the response code is correct.
        $I->seeResponseCodeIs(\Codeception\Util\HttpCode::OK);

        // Check the response is an array and contains data.
        $I->seeResponseIsJson();
        $I->assertArrayHasKey(
            'data',
            $response = \json_decode($I->grabResponse(), true)
        );
        $I->assertArrayHasKey(
            'contact',
            $response['data']
        );
    }

    /**
     * Get a random contact from another user than the authenticated one.
     *
     * @param \ApiTester $I - The API Tester.
     *
     * @return Contact
     */
    private function getRandomContactNotFrom(User $user): Contact
    {
        $attributes = ContactModel::query()
            ->where('user_id', '!=', $user->id())
            ->inRandomOrder()
            ->first()
            ->getAttributes();

        return new Contact($attributes);
    }

    /**
     * Try to get a contact to edit from another user.
     *
     * @param \ApiTester $I - The API Tester.
     *
     * @return void
     */
    public function getContactToEditViaApiOfOtherUser(\ApiTester $I): void
    {
        // Get a contact that the authenticated user has no access.
        $contact = $this->getRandomContactNotFrom($this->user);

        // Set the authorization header and the accept json.
        $I->haveHttpHeader('Accept', 'application/json');
        $I->haveHttpHeader('Authorization', $this->authToken);

        // Create an user with valid data.
        $I->sendGET('api/contacts/' . $contact->id() . '/edit');

        // Check the response code is correct.
        $I->seeResponseCodeIs(\Codeception\Util\HttpCode::FORBIDDEN);

        // Check the response is an array and contains a message.
        $I->seeResponseIsJson();
        $I->assertArrayHasKey(
            'message',
            \json_decode($I->grabResponse(), true)
        );
    }

    /**
     * Try to update a contact successfully.
     *
     * @param \ApiTester $I - The API Tester.
     *
     * @return void
     */
    public function updateContactViaApiOfCurrentUser(\ApiTester $I): void
    {
        // Set the authorization header and the accept json.
        $I->haveHttpHeader('Accept', 'application/json');
        $I->haveHttpHeader('Authorization', $this->authToken);

        // Change the created contact.
        $this->contact['last_name'] .= '.';

        // Create an user with valid data.
        $I->sendPUT('api/contacts/' . $this->contact['id'], $this->contact);

        // Check the response code is correct.
        $I->seeResponseCodeIs(\Codeception\Util\HttpCode::OK);

        // Check the response is an array and contains a message.
        $I->seeResponseIsJson();
        $I->assertArrayHasKey(
            'data',
            $response = \json_decode($I->grabResponse(), true)
        );
        $I->assertArrayHasKey(
            'contact',
            $response['data']
        );
    }

    /**
     * Try to update a contact with invalid data.
     *
     * @param \ApiTester $I - The API Tester.
     *
     * @return void
     */
    public function updateContactViaApiWithInvalidData(\ApiTester $I): void
    {
        // Set the authorization header and the accept json.
        $I->haveHttpHeader('Accept', 'application/json');
        $I->haveHttpHeader('Authorization', $this->authToken);

        // Create an user with valid data.
        $I->sendPUT('api/contacts/' . $this->contact['id'], []);

        // Check the response code is correct.
        $I->seeResponseCodeIs(\Codeception\Util\HttpCode::UNPROCESSABLE_ENTITY);

        // Check the response is an array and contains a message.
        $I->seeResponseIsJson();
        $I->assertArrayHasKey(
            'errors',
            \json_decode($I->grabResponse(), true)
        );
    }

    /**
     * Try to update a contact of other user.
     *
     * @param \ApiTester $I - The API Tester.
     *
     * @return void
     */
    public function updateContactViaApiOfOtherUser(\ApiTester $I): void
    {
        // Get a contact that the authenticated user has no access.
        $contact = $this->getRandomContactNotFrom($this->user)->getAttributes();

        // Set the authorization header and the accept json.
        $I->haveHttpHeader('Accept', 'application/json');
        $I->haveHttpHeader('Authorization', $this->authToken);

        // Change the created contact.
        $contact['last_name'] .= '.';

        // Create an user with valid data.
        $I->sendPUT('api/contacts/' . $contact['id'], $contact);

        // Check the response code is correct.
        $I->seeResponseCodeIs(\Codeception\Util\HttpCode::FORBIDDEN);

        // Check the response is an array and contains a message.
        $I->seeResponseIsJson();
        $I->assertArrayHasKey(
            'message',
            \json_decode($I->grabResponse(), true)
        );
    }

    /**
     * Try to update a contact without beeing authenticated.
     *
     * @param \ApiTester $I - The API Tester.
     *
     * @return void
     */
    public function updateContactViaApiWithoutAuthenticated(\ApiTester $I): void
    {
        // Set the authorization header and the accept json.
        $I->haveHttpHeader('Accept', 'application/json');

        // Change the created contact.
        $this->contact['last_name'] .= '.';

        // Create an user with valid data.
        $I->sendPUT('api/contacts/' . $this->contact['id'], $this->contact);

        // Check the response code is correct.
        $I->seeResponseCodeIs(\Codeception\Util\HttpCode::UNAUTHORIZED);

        // Check the response is an array and contains a message.
        $I->seeResponseIsJson();
        $I->assertArrayHasKey(
            'message',
            \json_decode($I->grabResponse(), true)
        );
    }

    /**
     * Try to delete a contact without authentication.
     *
     * @param \ApiTester $I - The API Tester.
     *
     * @return void
     */
    public function deleteContactViaApiWithoutAuthenticated(\ApiTester $I): void
    {
        // Set the authorization header and the accept json.
        $I->haveHttpHeader('Accept', 'application/json');

        // Create an user with valid data.
        $I->sendPUT('api/contacts/' . $this->contact['id'], $this->contact);

        // Check the response code is correct.
        $I->seeResponseCodeIs(\Codeception\Util\HttpCode::UNAUTHORIZED);

        // Check the response is an array and contains a message.
        $I->seeResponseIsJson();
        $I->assertArrayHasKey(
            'message',
            \json_decode($I->grabResponse(), true)
        );
    }

    /**
     * Try to delete a contact of another user.
     *
     * @param \ApiTester $I - The API Tester.
     *
     * @return void
     */
    public function deleteContactViaApiOfOtherUser(\ApiTester $I): void
    {
        // Get a contact that the authenticated user has no access.
        $contact = $this->getRandomContactNotFrom($this->user)->getAttributes();

        // Set the authorization header and the accept json.
        $I->haveHttpHeader('Accept', 'application/json');
        $I->haveHttpHeader('Authorization', $this->authToken);

        // Create an user with valid data.
        $I->sendDELETE('api/contacts/' . $contact['id']);

        // Check the response code is correct.
        $I->seeResponseCodeIs(\Codeception\Util\HttpCode::FORBIDDEN);

        // Check the response is an array and contains a message.
        $I->seeResponseIsJson();
        $I->assertArrayHasKey(
            'message',
            \json_decode($I->grabResponse(), true)
        );
    }

    /**
     * Try to delete a contact successfully.
     *
     * @param \ApiTester $I - The API Tester.
     *
     * @return void
     */
    public function deleteContactViaApiOfCurrentUser(\ApiTester $I): void
    {
        // Set the authorization header and the accept json.
        $I->haveHttpHeader('Accept', 'application/json');
        $I->haveHttpHeader('Authorization', $this->authToken);

        // Create an user with valid data.
        $I->sendDELETE('api/contacts/' . $this->contact['id']);

        // Check the response code is correct.
        $I->seeResponseCodeIs(\Codeception\Util\HttpCode::NO_CONTENT);
    }

    /**
     * Try to delete a contact that doesn't exists.
     *
     * @param \ApiTester $I - The API Tester.
     *
     * @return void
     */
    public function deleteContactViaApiThatDoesntExists(\ApiTester $I): void
    {
        // Set the authorization header and the accept json.
        $I->haveHttpHeader('Accept', 'application/json');
        $I->haveHttpHeader('Authorization', $this->authToken);

        // Create an user with valid data.
        $I->sendDELETE('api/contacts/' . $this->contact['id']);

        // Check the response code is correct.
        $I->seeResponseCodeIs(\Codeception\Util\HttpCode::NOT_FOUND);

        // Check the response is an array and contains a message.
        $I->seeResponseIsJson();
        $I->assertArrayHasKey(
            'message',
            \json_decode($I->grabResponse(), true)
        );
    }

    /**
     * Try to get a contact to edit that doesn't exists.
     *
     * @param \ApiTester $I - The API Tester.
     *
     * @return void
     */
    public function getContactToEditViaApiThatDoesntExists(\ApiTester $I): void
    {
        // Set the authorization header and the accept json.
        $I->haveHttpHeader('Accept', 'application/json');
        $I->haveHttpHeader('Authorization', $this->authToken);

        // Create an user with valid data.
        $I->sendGET('api/contacts/' . $this->contact['id'] . '/edit');

        // Check the response code is correct.
        $I->seeResponseCodeIs(\Codeception\Util\HttpCode::NOT_FOUND);

        // Check the response is an array and contains a message.
        $I->seeResponseIsJson();
        $I->assertArrayHasKey(
            'message',
            \json_decode($I->grabResponse(), true)
        );
    }

    /**
     * Try to update a contact that doesn't exists.
     *
     * @param \ApiTester $I - The API Tester.
     *
     * @return void
     */
    public function updateContactViaApiThatDoesNotExists(\ApiTester $I): void
    {
        // Set the authorization header and the accept json.
        $I->haveHttpHeader('Accept', 'application/json');
        $I->haveHttpHeader('Authorization', $this->authToken);

        // Change the created contact.
        $this->contact['last_name'] .= '.';

        // Create an user with valid data.
        $I->sendPUT('api/contacts/' . $this->contact['id'], $this->contact);

        // Check the response code is correct.
        $I->seeResponseCodeIs(\Codeception\Util\HttpCode::NOT_FOUND);

        // Check the response is an array and contains a message.
        $I->seeResponseIsJson();
        $I->assertArrayHasKey(
            'message',
            \json_decode($I->grabResponse(), true)
        );
    }
}
