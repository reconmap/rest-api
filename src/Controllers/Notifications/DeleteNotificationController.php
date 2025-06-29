<?php declare(strict_types=1);

namespace Reconmap\Controllers\Notifications;

use OpenApi\Attributes as OpenApi;
use Reconmap\Controllers\DeleteEntityController;
use Reconmap\Http\Docs\Default204NoContentResponse;
use Reconmap\Http\Docs\Default403UnauthorisedResponse;
use Reconmap\Http\Docs\InPathIdParameter;
use Reconmap\Models\AuditActions\AuditActions;
use Reconmap\Repositories\NotificationsRepository;
use Reconmap\Services\ActivityPublisherService;
use Reconmap\Services\Security\AuthorisationService;

#[OpenApi\Delete(path: "/notifications/{notificationId}", description: "Deletes notification with the given id", security: ["bearerAuth"], tags: ["Notifications"], parameters: [new InPathIdParameter("notificationId")])]
#[Default204NoContentResponse]
#[Default403UnauthorisedResponse]
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
