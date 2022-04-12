<?php declare(strict_types=1);

namespace Reconmap\Controllers\Commands;

use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Controllers\SecureController;
use Reconmap\Repositories\CommandRepository;
use Reconmap\Repositories\SearchCriterias\CommandSearchCriteria;
use Reconmap\Services\PaginationRequestHandler;
use Reconmap\Services\Security\AuthorisationService;

class GetCommandsController extends SecureController
{
    public function __construct(AuthorisationService $authorisationService,
                                private              readonly CommandRepository $repository,
                                private              readonly CommandSearchCriteria $searchCriteria)
    {
        parent::__construct($authorisationService);
    }

    public function process(ServerRequestInterface $request): ResponseInterface
    {
        $params = $request->getQueryParams();
        if (isset($params['keywords'])) {
            $this->searchCriteria->addKeywordsCriterion($params['keywords']);
        }

        $paginator = new PaginationRequestHandler($request);

        $commands = $this->repository->search($this->searchCriteria, $paginator);
        $count = $this->repository->count($this->searchCriteria);
        $pageCount = $paginator->calculatePageCount($count);

        $response = new Response;
        $response->getBody()->write(json_encode($commands));
        return $response
            ->withHeader('Access-Control-Expose-Headers', 'X-Total-Count,X-Page-Count')
            ->withHeader('X-Total-Count', $count)
            ->withHeader('X-Page-Count', $pageCount);
    }
}
