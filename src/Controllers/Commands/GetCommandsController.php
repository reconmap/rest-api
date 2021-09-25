<?php declare(strict_types=1);

namespace Reconmap\Controllers\Commands;

use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Controllers\Controller;
use Reconmap\Repositories\CommandRepository;
use Reconmap\Repositories\SearchCriterias\CommandSearchCriteria;
use Reconmap\Services\PaginationRequestHandler;

class GetCommandsController extends Controller
{
    public function __construct(private CommandRepository     $repository,
                                private CommandSearchCriteria $searchCriteria)
    {
    }

    public function __invoke(ServerRequestInterface $request): array
    {
        $params = $request->getQueryParams();
        if (isset($params['keywords'])) {
            $this->searchCriteria->addKeywordsCriterion($params['keywords']);
        }

        $paginator = new PaginationRequestHandler($request);

        return $this->repository->search($this->searchCriteria, $paginator);
    }
}
