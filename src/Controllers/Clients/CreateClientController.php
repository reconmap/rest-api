<?php declare(strict_types=1);

namespace Reconmap\Controllers\Clients;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Controllers\Controller;
use Reconmap\Models\Client;
use Reconmap\Models\Contact;
use Reconmap\Repositories\ClientContactRepository;
use Reconmap\Repositories\ClientRepository;
use Reconmap\Repositories\ContactRepository;

class CreateClientController extends Controller
{
    public function __construct(private readonly ClientRepository $repository,
                                private readonly ContactRepository $contactRepository,
                                private readonly ClientContactRepository $clientContactRepository)
    {
    }

    public function __invoke(ServerRequestInterface $request): ResponseInterface
    {
        /** @var Client $client */
        $client = $this->getJsonBodyDecodedAsClass($request, new Client());
        $client->creator_uid = $request->getAttribute('userId');

        $client->id = $this->repository->insert($client);

        $body = json_decode((string)$request->getBody(), false);

        $contact = new Contact();
        $contact->kind = $body->contact_kind;
        $contact->name = $body->contact_name;
        $contact->email = $body->contact_email;
        $contact->phone = $body->contact_phone;
        $contact->role = $body->contact_role;
        $contact->id = $this->contactRepository->insert($contact);

        $this->clientContactRepository->create($client->id, $contact->id);

        return $this->createStatusCreatedResponse($client);
    }
}
