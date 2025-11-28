<?php declare(strict_types=1);

namespace Reconmap\Controllers\Contacts;

use OpenApi\Attributes as OpenApi;
use Reconmap\Controllers\DeleteEntityController;
use Reconmap\Http\Docs\Default204NoContentResponse;
use Reconmap\Http\Docs\Default403UnauthorisedResponse;
use Reconmap\Http\Docs\InPathIdParameter;
use Reconmap\Models\AuditActions\AuditActions;
use Reconmap\Repositories\ContactRepository;
use Reconmap\Services\ActivityPublisherService;
use Reconmap\Services\Security\AuthorisationService;

#[OpenApi\Delete(path: "/contacts/{contactId}", description: "Deletes contact with the given id", security: ["bearerAuth"], tags: ["Contacts"], parameters: [new InPathIdParameter("contactId")])]
#[Default204NoContentResponse]
#[Default403UnauthorisedResponse]
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
