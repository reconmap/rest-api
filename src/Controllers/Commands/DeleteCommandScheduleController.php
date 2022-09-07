<?php declare(strict_types=1);

namespace Reconmap\Controllers\Commands;

use Reconmap\Controllers\DeleteEntityController;
use Reconmap\Models\AuditActions\DocumentAuditActions;
use Reconmap\Repositories\CommandScheduleRepository;
use Reconmap\Services\ActivityPublisherService;
use Reconmap\Services\Security\AuthorisationService;

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
            DocumentAuditActions::DELETED,
            'commandScheduleId'
        );
    }
}
