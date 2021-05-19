<?php declare(strict_types=1);

namespace Reconmap\Services;

use Psr\Http\Message\ServerRequestInterface;

class RequestPaginator
{
    private array $queryParams;

    public function __construct(ServerRequestInterface $request,
                                private int $resultsPerPage = 20)
    {
        $this->queryParams = $request->getQueryParams();
    }

    public function getCurrentPage(): int
    {
        return isset($this->queryParams['page']) ? intval($this->queryParams['page']) : 0;
    }

    public function calculatePageCount(int $totalResults): int
    {
        return max((int)ceil($totalResults / 20), 1);
    }
}

