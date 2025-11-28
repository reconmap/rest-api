<?php declare(strict_types=1);

namespace Reconmap\Controllers\Documents;

use OpenApi\Attributes as OpenApi;
use Reconmap\Controllers\DeleteEntityController;
use Reconmap\Http\Docs\Default204NoContentResponse;
use Reconmap\Http\Docs\Default403UnauthorisedResponse;
use Reconmap\Http\Docs\InPathIdParameter;
use Reconmap\Models\AuditActions\AuditActions;
use Reconmap\Repositories\DocumentRepository;
use Reconmap\Services\ActivityPublisherService;
use Reconmap\Services\Security\AuthorisationService;

#[OpenApi\Delete(path: "/documents/{documentId}", description: "Deletes contact with the given id", security: ["bearerAuth"], tags: ["Documents"], parameters: [new InPathIdParameter("documentId")])]
#[Default204NoContentResponse]
#[Default403UnauthorisedResponse]
class DeleteDocumentController extends DeleteEntityController
{
    public function __construct(
        private readonly AuthorisationService     $authorisationService,
        private readonly ActivityPublisherService $activityPublisherService,
        private readonly DocumentRepository       $repository,
    )
    {
        parent::__construct(
            $this->authorisationService,
            $this->activityPublisherService,
            $this->repository,
            'document',
            AuditActions::DELETED,
            'documentId'
        );
    }
}
