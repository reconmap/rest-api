<?php declare(strict_types=1);

namespace Reconmap\Services;

use Psr\Http\Message\ServerRequestInterface;

class PaginationRequestHandler
{
    private array $queryParams;

    public function __construct(ServerRequestInterface $request,
                                private int            $resultsPerPage = 20)
    {
        $this->queryParams = $request->getQueryParams();
    }

    public function getCurrentPage(): int
    {
        return isset($this->queryParams['page']) ? intval($this->queryParams['page']) : 0;
    }

    public function calculatePageCount(int $totalResults): int
    {
        return max((int)ceil($totalResults / $this->getLimitPerPage()), 1);
    }

    public function getLimitPerPage(): int
    {
        return isset($this->queryParams['limit']) ? intval($this->queryParams['limit']) : $this->resultsPerPage;
    }

    public function getLimitOffset(): int
    {
        return $this->getCurrentPage() * $this->getLimitPerPage();
    }
}

