<?php declare(strict_types=1);

namespace Reconmap\Controllers\Documents;

use Reconmap\Controllers\DeleteEntityController;
use Reconmap\Models\AuditActions\AuditActions;
use Reconmap\Models\AuditActions\DocumentAuditActions;
use Reconmap\Repositories\DocumentRepository;
use Reconmap\Services\ActivityPublisherService;
use Reconmap\Services\Security\AuthorisationService;

class DeleteDocumentController extends DeleteEntityController
{
    public function __construct(
        private AuthorisationService     $authorisationService,
        private ActivityPublisherService $activityPublisherService,
        private DocumentRepository       $repository,
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
