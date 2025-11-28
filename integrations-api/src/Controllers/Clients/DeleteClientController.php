<?php declare(strict_types=1);

namespace Reconmap\Controllers\Clients;

use OpenApi\Attributes as OpenApi;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Controllers\Controller;
use Reconmap\Http\Docs\Default204NoContentResponse;
use Reconmap\Http\Docs\Default403UnauthorisedResponse;
use Reconmap\Http\Docs\InPathIdParameter;
use Reconmap\Models\AuditActions\AuditActions;
use Reconmap\Repositories\ClientRepository;
use Reconmap\Services\ActivityPublisherService;


#[OpenApi\Delete(path: "/clients/{clientId}", description: "Deletes client with the given id", security: ["bearerAuth"], tags: ["Organisations"], parameters: [new InPathIdParameter("clientId")])]
#[Default204NoContentResponse]
#[Default403UnauthorisedResponse]
class DeleteClientController extends Controller
{
    public function __construct(
        private readonly ClientRepository         $repository,
        private readonly ActivityPublisherService $activityPublisherService)
    {
    }

    public function __invoke(ServerRequestInterface $request, array $args): ResponseInterface
    {
        $clientId = (int)$args['clientId'];

        $success = $this->repository->deleteById($clientId);

        $userId = $request->getAttribute('userId');
        $this->auditAction($userId, $clientId);

        return $success ? $this->createNoContentResponse() : $this->createInternalServerErrorResponse();
    }

    private function auditAction(int $loggedInUserId, int $clientId): void
    {
        $this->activityPublisherService->publish($loggedInUserId, AuditActions::DELETED, 'Client', ['id' => $clientId]);
    }
}
