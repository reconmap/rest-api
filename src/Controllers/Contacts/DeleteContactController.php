<?php declare(strict_types=1);

namespace Reconmap\Controllers\Contacts;

use Reconmap\Controllers\DeleteEntityController;
use Reconmap\Models\AuditActions\AuditActions;
use Reconmap\Repositories\ContactRepository;
use Reconmap\Services\ActivityPublisherService;
use Reconmap\Services\Security\AuthorisationService;

class DeleteContactController extends DeleteEntityController
{
    public function __construct(
        AuthorisationService     $authorisationService,
        ActivityPublisherService $activityPublisherService,
        ContactRepository        $repository,
    )
    {
        parent::__construct(
            $authorisationService,
            $activityPublisherService,
            $repository,
            'contact',
            AuditActions::DELETED,
            'contactId'
        );
    }
}
