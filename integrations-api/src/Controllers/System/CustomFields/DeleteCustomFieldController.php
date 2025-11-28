<?php declare(strict_types=1);

namespace Reconmap\Controllers\System\CustomFields;

use OpenApi\Attributes as OpenApi;
use Reconmap\Controllers\DeleteEntityController;
use Reconmap\Http\Docs\Default204NoContentResponse;
use Reconmap\Http\Docs\Default403UnauthorisedResponse;
use Reconmap\Http\Docs\InPathIdParameter;
use Reconmap\Models\AuditActions\AuditActions;
use Reconmap\Repositories\CustomFieldRepository;
use Reconmap\Services\ActivityPublisherService;
use Reconmap\Services\Security\AuthorisationService;

#[OpenApi\Delete(path: "/system/custom-fields/{fieldId}", description: "Deletes custom field with the given id", security: ["bearerAuth"], tags: ["Custom fields"], parameters: [new InPathIdParameter("fieldId")])]
#[Default204NoContentResponse]
#[Default403UnauthorisedResponse]
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
            AuditActions::DELETED,
            'fieldId'
        );
    }
}
