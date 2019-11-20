<?php
namespace NunoLopes\LaravelContactsAPI\Controllers;

use Illuminate\Http\Response;
use NunoLopes\DomainContacts\Exceptions\BaseException;
use NunoLopes\DomainContacts\Exceptions\ForbiddenException;
use NunoLopes\DomainContacts\Exceptions\Repositories\Contacts\ContactNotFoundException;
use NunoLopes\DomainContacts\Exceptions\Repositories\Contacts\ContactNotUpdatedException;
use NunoLopes\DomainContacts\Exceptions\UnauthorizedException;
use NunoLopes\DomainContacts\Requests\Contacts\CreateContactRequest;
use NunoLopes\DomainContacts\Requests\Contacts\DeleteContactRequest;
use NunoLopes\DomainContacts\Requests\Contacts\UpdateContactRequest;
use NunoLopes\DomainContacts\Services\ContactsService;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Class ContactsController.
 *
 * This class will be responsible for Contacts actions.
 *
 * @todo Handle exceptions with try/catch and right status code.
 *
 * @package NunoLopes\LaravelContactsAPI
 */
class ContactsController
{
    /**
     * @var ContactsService - ContactsService Instance.
     */
    private $contactsService = null;

    /**
     * ContactsServices constructor.
     *
     * @param ContactsService $contactsService - ContactsService Instance.
     */
    public function __construct(ContactsService $contactsService)
    {
        $this->contactsService = $contactsService;
    }

    /**
     * Display the current user's contacts.
     *
     * @return Response
     */
    public function index(): Response
    {
        // Get all contacts.
        try {
            $contacts = $this->contactsService->listAllContactsOfAuthenticatedUser();
        } catch (BaseException $e) {
            abort($e->getCode(), $e->getMessage());
        }

        return response()
            ->view(
                'laravel-contacts-api::contacts.index',
                [ 'contacts' => $contacts ],
                200
            );
    }

    /**
     * Store a newly created Contact in storage.
     *
     * @param  CreateContactRequest  $request - Request instance with the validated data.
     *
     * @throws UnauthorizedException - If the user is a guest.
     *
     * @return int
     */
    public function store(CreateContactRequest $request)
    {
        if ($request->fails()) {
            return response([ 'errors' => $request->errors() ], 422);
        }

        try {
            $contact = $this->contactsService->create($request->validated());
        } catch (BaseException $e) {
            abort($e->getCode(), $e->getMessage());
        }

        return response()
            ->view(
                'laravel-contacts-api::contacts.edit',
                [ 'contact' => $contact ],
                200
            );
    }

    /**
     * Show the data for editing the specified Contact.
     *
     * @param  int  $id - ID of the Contact that is going to be edited.
     *
     * @throws ContactNotFoundException - If the contact doesn't exist.
     * @throws ForbiddenException       - If the user doesn't own the contact.
     *
     * @return Response
     */
    public function edit(int $id): Response
    {
        // Retrieve the contact from the database to check
        // if its owner matches the logged in user.
        try {
            $contact = $this->contactsService->edit($id);
        } catch (BaseException $e) {
            abort($e->getCode(), $e->getMessage());
        }

        return response()
            ->view(
                'laravel-contacts-api::contacts.edit',
                [ 'contact' => $contact ],
                200
            );
    }

    /**
     * Update the specified Contact in storage.
     *
     * @param  UpdateContactRequest $request - Request instance with the validated data.
     *
     * @throws UnauthorizedException      - If the user is a guest.
     * @throws ContactNotUpdatedException - If the contact was not updated.
     * @throws ForbiddenException         - If the user doesn't own the contact.
     *
     * @return Response
     */
    public function update(UpdateContactRequest $request): Response
    {
        if ($request->fails()) {
            return response([ 'errors' => $request->errors() ], 422);
        }

        try{
            // Retrieve the contact from the database to check
            // if its owner matches the logged in user.
            $contact = $this->contactsService->update($request->id(), $request->validated());
        } catch (BaseException $e) {
            abort($e->getCode(), $e->getMessage());
        }

        return response()
            ->view(
                'laravel-contacts-api::contacts.edit',
                [ 'contact' => $contact ],
                200
            );
    }

    /**
     * Remove the specified Contact from storage.
     *
     * @param  int  $id - Id of the Contact that is going to be destroyed.
     *
     * @throws HttpException - If the contact wasn't destroyed.
     *
     * @return Response
     */
    public function destroy(DeleteContactRequest $request): Response
    {
        try {
            $this->contactsService->destroy($request->id());
        } catch (BaseException $e) {
            abort($e->getCode(), $e->getMessage());
        }

        // Retrieve the contact from the database to check
        // if its owner matches the logged in user.
        return response(null, 204);
    }
}
