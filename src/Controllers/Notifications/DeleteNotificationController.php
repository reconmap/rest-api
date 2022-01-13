<?php declare(strict_types=1);

namespace Reconmap\Controllers\Notifications;

use Reconmap\Controllers\DeleteEntityController;
use Reconmap\Models\AuditActions\NotificationAuditActions;
use Reconmap\Repositories\NotificationsRepository;
use Reconmap\Services\ActivityPublisherService;
use Reconmap\Services\Security\AuthorisationService;

class DeleteNotificationController extends DeleteEntityController
{
    public function __construct(
        private AuthorisationService     $authorisationService,
        private ActivityPublisherService $activityPublisherService,
        private NotificationsRepository  $repository,
    )
    {
        parent::__construct(
            $this->authorisationService,
            $this->activityPublisherService,
            $this->repository,
            'notification',
            NotificationAuditActions::DELETED,
            'notificationId'
        );
    }
}
