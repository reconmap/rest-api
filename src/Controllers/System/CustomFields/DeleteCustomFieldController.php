<?php declare(strict_types=1);

namespace Reconmap\Controllers\System\CustomFields;

use Reconmap\Controllers\DeleteEntityController;
use Reconmap\Models\AuditActions\ContactAuditActions;
use Reconmap\Repositories\CustomFieldRepository;
use Reconmap\Services\ActivityPublisherService;
use Reconmap\Services\Security\AuthorisationService;

class DeleteCustomFieldController extends DeleteEntityController
{
    public function __construct(
        AuthorisationService     $authorisationService,
        ActivityPublisherService $activityPublisherService,
        CustomFieldRepository    $repository,
    )
    {
        parent::__construct(
            $authorisationService,
            $activityPublisherService,
            $repository,
            'custom_field',
            ContactAuditActions::DELETED,
            'fieldId'
        );
    }
}
