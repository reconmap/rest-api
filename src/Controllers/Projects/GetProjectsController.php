<?php declare(strict_types=1);

namespace Reconmap\Controllers\Projects;

use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Controllers\Controller;
use Reconmap\Repositories\ProjectRepository;
use Reconmap\Repositories\SearchCriterias\ProjectSearchCriteria;
use Reconmap\Services\PaginationRequestHandler;

class GetProjectsController extends Controller
{
    public function __construct(private readonly ProjectRepository $projectRepository,
                                private readonly ProjectSearchCriteria $projectSearchCriteria)
    {
    }

    public function __invoke(ServerRequestInterface $request): array
    {
        $params = $request->getQueryParams();

        $user = $this->getUserFromRequest($request);

        if (isset($params['keywords'])) {
            $this->projectSearchCriteria->addKeywordsCriterion($params['keywords']);
        }
        if (isset($params['clientId'])) {
            $this->projectSearchCriteria->addClientCriterion(intval($params['clientId']));
        }
        if (isset($params['status'])) {
            $archived = 'archived' === $params['status'];
            $this->projectSearchCriteria->addArchivedCriterion($archived);
        }
        if (isset($params['isTemplate'])) {
            $isTemplate = filter_var($params['isTemplate'], FILTER_VALIDATE_BOOL);
            $this->projectSearchCriteria->addTemplateCriterion($isTemplate);
        } else {
            $this->projectSearchCriteria->addIsNotTemplateCriterion();
        }
        if (!$user->isAdministrator()) {
            $this->projectSearchCriteria->addUserCriterion($user->id);
        }

        $paginator = new PaginationRequestHandler($request);
        return $this->projectRepository->search($this->projectSearchCriteria, $paginator);
    }
}
