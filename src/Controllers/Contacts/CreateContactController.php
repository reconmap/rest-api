<?php declare(strict_types=1);

namespace Reconmap\Controllers\Contacts;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Controllers\Controller;
use Reconmap\Models\Contact;
use Reconmap\Repositories\ClientContactRepository;
use Reconmap\Repositories\ContactRepository;

class CreateContactController extends Controller
{
    public function __construct(private readonly ContactRepository       $repository,
                                private readonly ClientContactRepository $clientContactRepository)
    {
    }

    public function __invoke(ServerRequestInterface $request, array $args): ResponseInterface
    {
        /** @var Contact $contact */
        $contact = $this->getJsonBodyDecodedAsClass($request, new Contact());
        $contact->creator_uid = $request->getAttribute('userId');
        $contact->id = $this->repository->insert($contact);

        if (isset($args['clientId'])) {
            $clientId = intval($args['clientId']);

            $this->clientContactRepository->create($clientId, $contact->id);
        }

        return $this->createStatusCreatedResponse($contact);
    }
}
