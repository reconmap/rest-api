<?php declare(strict_types=1);

namespace Reconmap\Controllers\Projects;

use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Controllers\Controller;
use Reconmap\Events\SearchEvent;
use Reconmap\Repositories\ProjectRepository;
use Reconmap\Repositories\SearchCriterias\ProjectSearchCriteria;
use Reconmap\Services\PaginationRequestHandler;
use Symfony\Component\EventDispatcher\EventDispatcher;

class GetProjectsController extends Controller
{
    public function __construct(private readonly ProjectRepository     $projectRepository,
                                private readonly ProjectSearchCriteria $projectSearchCriteria,
                                private readonly EventDispatcher       $eventDispatcher)
    {
    }

    public function __invoke(ServerRequestInterface $request): ResponseInterface
    {
        $params = $request->getQueryParams();

        $user = $this->getUserFromRequest($request);

        if (isset($params['keywords'])) {
            $this->projectSearchCriteria->addKeywordsCriterion($params['keywords']);
            $this->eventDispatcher->dispatch(new SearchEvent($user->id, $params['keywords']));
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
        }
        if (!$user->isAdministrator()) {
            $this->projectSearchCriteria->addUserCriterion($user->id);
        }

        $paginateResults = isset($params['page']);
        $paginator = $paginateResults ? new PaginationRequestHandler($request) : null;

        $projects = $this->projectRepository->search($this->projectSearchCriteria, $paginator);

        $response = new Response;
        $response->getBody()->write(json_encode($projects));

        if ($paginateResults) {
            $count = $this->projectRepository->count($this->projectSearchCriteria);
            $pageCount = $paginator->calculatePageCount($count);

            return $response
                ->withHeader('Access-Control-Expose-Headers', 'X-Total-Count,X-Page-Count')
                ->withHeader('X-Total-Count', $count)
                ->withHeader('X-Page-Count', $pageCount);
        }

        return $response;
    }
}
