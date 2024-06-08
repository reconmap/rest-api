<?php declare(strict_types=1);

namespace Reconmap\Controllers\Clients;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Controllers\Controller;
use Reconmap\Models\AuditActions\ClientAuditActions;
use Reconmap\Models\Client;
use Reconmap\Repositories\ClientRepository;
use Reconmap\Services\ActivityPublisherService;

class CreateClientController extends Controller
{
    public function __construct(private readonly ClientRepository $repository, private readonly ActivityPublisherService $activityPublisherService)
    {
    }

    public function __invoke(ServerRequestInterface $request): ResponseInterface
    {
        $loggedInUserId = $request->getAttribute('userId');

        /** @var Client $client */
        $client = $this->getJsonBodyDecodedAsClass($request, new Client());
        $client->creator_uid = $loggedInUserId;

        $client->id = $this->repository->insert($client);

        $this->auditAction($loggedInUserId, $client);

        return $this->createStatusCreatedResponse($client);
    }

    private function auditAction(int $loggedInUserId, Client $client): void
    {
        $this->activityPublisherService->publish($loggedInUserId, ClientAuditActions::CREATED, ['type' => 'client', 'id' => $client->id, 'name' => $client->name]);
    }
}
