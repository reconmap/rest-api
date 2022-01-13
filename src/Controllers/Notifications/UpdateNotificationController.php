<?php declare(strict_types=1);

namespace Reconmap\Controllers\Notifications;

use Reconmap\Controllers\UpdateEntityController;
use Reconmap\Models\AuditActions\NotificationAuditActions;
use Reconmap\Repositories\NotificationsRepository;
use Reconmap\Services\ActivityPublisherService;
use Reconmap\Services\Security\AuthorisationService;

class UpdateNotificationController extends UpdateEntityController
{
    public function __construct(AuthorisationService $authorisationService, ActivityPublisherService $activityPublisherService, NotificationsRepository $repository)
    {
        parent::__construct($authorisationService, $activityPublisherService, $repository, 'notification', NotificationAuditActions::UPDATED, 'notificationId');
    }
}
