<?php declare(strict_types=1);

namespace Reconmap\Controllers\Projects;

use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Controllers\Controller;
use Reconmap\Database\NullColumnReplacer;
use Reconmap\Models\AuditActions\AuditActions;
use Reconmap\Repositories\ProjectRepository;
use Reconmap\Services\ActivityPublisherService;

class UpdateProjectController extends Controller
{
    public function __construct(private readonly ProjectRepository        $repository,
                                private readonly ActivityPublisherService $activityPublisherService)
    {
    }

    public function __invoke(ServerRequestInterface $request, array $args): array
    {
        $projectId = (int)$args['projectId'];

        $requestBody = $this->getJsonBodyDecodedAsArray($request);
        $newColumnValues = array_filter(
            $requestBody,
            fn(string $key) => in_array($key, array_keys(ProjectRepository::UPDATABLE_COLUMNS_TYPES)),
            ARRAY_FILTER_USE_KEY
        );

        $success = false;
        if (!empty($newColumnValues)) {
            NullColumnReplacer::replaceEmptyWithNulls(['category_id', 'client_id', 'engagement_start_date', 'engagement_end_date', 'external_id', 'vulnerability_metrics'], $newColumnValues);

            $success = $this->repository->updateById($projectId, $newColumnValues);

            $loggedInUserId = $request->getAttribute('userId');
            $this->auditAction($loggedInUserId, $projectId);
        }

        return ['success' => $success];
    }

    private function auditAction(int $loggedInUserId, int $projectId): void
    {
        $this->activityPublisherService->publish($loggedInUserId, AuditActions::UPDATED, 'Project', ['id' => $projectId]);
    }
}
