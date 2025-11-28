<?php declare(strict_types=1);

namespace Reconmap\Controllers\Commands;

use OpenApi\Attributes as OpenApi;
use Reconmap\Controllers\DeleteEntityController;
use Reconmap\Http\Docs\Default204NoContentResponse;
use Reconmap\Http\Docs\Default403UnauthorisedResponse;
use Reconmap\Http\Docs\InPathIdParameter;
use Reconmap\Models\AuditActions\AuditActions;
use Reconmap\Repositories\CommandScheduleRepository;
use Reconmap\Services\ActivityPublisherService;
use Reconmap\Services\Security\AuthorisationService;

#[OpenApi\Delete(path: "/commands/schedules/{commandId}", description: "Deletes command schedule with the given id", security: ["bearerAuth"], tags: ["Commands"], parameters: [new InPathIdParameter("commandId")])]
#[Default204NoContentResponse]
#[Default403UnauthorisedResponse]
class DeleteCommandScheduleController extends DeleteEntityController
{
    public function __construct(
        private readonly AuthorisationService      $authorisationService,
        private readonly ActivityPublisherService  $activityPublisherService,
        private readonly CommandScheduleRepository $repository,
    )
    {
        parent::__construct(
            $this->authorisationService,
            $this->activityPublisherService,
            $this->repository,
            'command_schedule',
            AuditActions::DELETED,
            'commandScheduleId'
        );
    }
}
