<?php declare(strict_types=1);

namespace Reconmap\Controllers\Notifications;

use Reconmap\Controllers\DeleteEntityController;
use Reconmap\Models\AuditActions\AuditActions;
use Reconmap\Repositories\NotificationsRepository;
use Reconmap\Services\ActivityPublisherService;
use Reconmap\Services\Security\AuthorisationService;

class DeleteNotificationController extends DeleteEntityController
{
    public function __construct(
        private readonly AuthorisationService     $authorisationService,
        private readonly ActivityPublisherService $activityPublisherService,
        private readonly NotificationsRepository  $repository,
    )
    {
        parent::__construct(
            $this->authorisationService,
            $this->activityPublisherService,
            $this->repository,
            'notification',
            AuditActions::DELETED,
            'notificationId'
        );
    }
}
