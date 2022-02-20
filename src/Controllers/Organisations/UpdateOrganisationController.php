<?php declare(strict_types=1);

namespace Reconmap\Controllers\Organisations;

use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Controllers\Controller;
use Reconmap\Models\AuditActions\AuditLogAction;
use Reconmap\Models\Organisation;
use Reconmap\Repositories\OrganisationRepository;
use Reconmap\Services\ActivityPublisherService;

class UpdateOrganisationController extends Controller
{
    public function __construct(private readonly OrganisationRepository $repository,
                                private readonly ActivityPublisherService $activityPublisherService)
    {
    }

    public function __invoke(ServerRequestInterface $request): array
    {
        $organisation = $this->getJsonBodyDecodedAsClass($request, new Organisation());

        $success = $this->repository->updateRootOrganisation($organisation);

        $loggedInUserId = $request->getAttribute('userId');

        $this->auditAction($loggedInUserId);

        return ['success' => $success];
    }

    private function auditAction(int $loggedInUserId): void
    {
        $this->activityPublisherService->publish($loggedInUserId, AuditLogAction::ORGANISATION_UPDATED);
    }
}
