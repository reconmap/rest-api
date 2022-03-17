<?php declare(strict_types=1);

namespace Reconmap\Controllers\Commands;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Repositories\CommandRepository;
use Reconmap\Repositories\SearchCriterias\CommandSearchCriteria;
use Reconmap\SecureController;
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

    public function process(ServerRequestInterface $request): array|ResponseInterface
    {
        $params = $request->getQueryParams();
        if (isset($params['keywords'])) {
            $this->searchCriteria->addKeywordsCriterion($params['keywords']);
        }

        $paginator = new PaginationRequestHandler($request);

        return $this->repository->search($this->searchCriteria, $paginator);
    }
}
