<?php declare(strict_types=1);

namespace Reconmap\Controllers\Notifications;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Controllers\Controller;
use Reconmap\Models\AuditActions\AuditActions;
use Reconmap\Repositories\NotificationsRepository;
use Reconmap\Services\AuditLogService;

class BulkUpdateNotificationsController extends Controller
{
    public function __construct(private readonly NotificationsRepository $notificationsRepository,
                                private readonly AuditLogService         $auditLogService)
    {
    }

    public function __invoke(ServerRequestInterface $request): ResponseInterface
    {
        $loggedInUserId = $request->getAttribute('userId');

        $requestData = $this->getJsonBodyDecodedAsArray($request);
        $notificationIds = $requestData['notificationIds'];

        if (!$this->notificationsRepository->bulkUpdateStatusByUserId($notificationIds, 'read', $loggedInUserId)) {
            $this->logger->error('Failed to update notifications');
            return $this->createInternalServerErrorResponse();
        }

        $this->auditLogService->insert($loggedInUserId, AuditActions::UPDATED, 'Notification', ['ids' => $notificationIds]);

        return $this->createNoContentResponse();
    }
}
