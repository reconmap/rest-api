<?php declare(strict_types=1);

namespace Reconmap\Controllers\Notes;

use OpenApi\Attributes as OpenApi;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Controllers\Controller;
use Reconmap\Http\Docs\Default204NoContentResponse;
use Reconmap\Http\Docs\Default403UnauthorisedResponse;
use Reconmap\Http\Docs\InPathIdParameter;
use Reconmap\Models\AuditActions\AuditActions;
use Reconmap\Repositories\NoteRepository;
use Reconmap\Services\ActivityPublisherService;
use Reconmap\Services\Security\AuthorisationService;
use Symfony\Component\String\Inflector\EnglishInflector;

#[OpenApi\Delete(path: "/notes/{noteId}", description: "Deletes note with the given id", security: ["bearerAuth"], tags: ["Notes"], parameters: [new InPathIdParameter("noteId")])]
#[Default204NoContentResponse]
#[Default403UnauthorisedResponse]
class DeleteNoteController extends Controller
{
    public function __construct(private readonly AuthorisationService     $authorisationService,
                                private readonly ActivityPublisherService $activityPublisherService,
                                private readonly NoteRepository           $repository)
    {
    }

    public function __invoke(ServerRequestInterface $request, array $args): ResponseInterface
    {
        $entityPlural = explode('/', $request->getUri()->getPath())[1];
        $englishInflector = new EnglishInflector();
        $singulars = $englishInflector->singularize($entityPlural);
        $entity = array_pop($singulars);

        $operation = $entity . '.delete';

        $role = $request->getAttribute('role');
        if (!$this->authorisationService->isRoleAllowed($role, $operation)) {
            $this->logger->warning("Unauthorised action '" . $operation . "' called for role '$role'");

            return $this->createForbiddenResponse();
        }

        $entityId = (int)$args['entityId'];

        $success = $this->repository->deleteById($entityId);

        if ($success) {
            $loggedInUserId = $request->getAttribute('userId');
            $this->activityPublisherService->publish($loggedInUserId, AuditActions::DELETED, 'Note', ['id' => $entityId]);

            return $this->createNoContentResponse();
        }

        $this->logger->warning("Failed to delete $entity with id $entityId");;
        return $this->createInternalServerErrorResponse();
    }
}
