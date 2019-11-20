<?=
/** @var array $contacts - All contacts of the authenticated user. */

\json_encode([
    'data' => [
        'contacts' => $contacts
    ]
]);
