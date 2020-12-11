<?php declare(strict_types=1);

namespace Reconmap\Controllers\Organisations;

use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Controllers\Controller;
use Reconmap\Models\AuditLogAction;
use Reconmap\Models\Organisation;
use Reconmap\Repositories\OrganisationRepository;
use Reconmap\Services\ActivityPublisherService;

class UpdateOrganisationController extends Controller
{
    public function __invoke(ServerRequestInterface $request): array
    {
        /** @var Organisation $organisation */
        $organisation = $this->getJsonBodyDecoded($request);

        $repository = new OrganisationRepository($this->db);
        $success = $repository->updateRootOrganisation($organisation);

        $loggedInUserId = $request->getAttribute('userId');

        $this->auditAction($loggedInUserId);

        return ['success' => $success];
    }

    private function auditAction(int $loggedInUserId): void
    {
        $activityPublisherService = $this->container->get(ActivityPublisherService::class);
        $activityPublisherService->publish($loggedInUserId, AuditLogAction::ORGANISATION_UPDATED);
    }
}
