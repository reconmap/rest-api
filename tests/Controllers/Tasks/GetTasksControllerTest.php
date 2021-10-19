<?php declare(strict_types=1);

namespace Reconmap\Controllers\Tasks;

use PHPUnit\Framework\TestCase;
use Ponup\SqlBuilders\SearchCriteria;
use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Repositories\TaskRepository;
use Reconmap\Services\PaginationRequestHandler;

class GetTasksControllerTest extends TestCase
{
    public function testHappyPath()
    {
        $mockRequest = $this->createMock(ServerRequestInterface::class);
        $mockRequest->expects($this->exactly(3))
            ->method('getQueryParams')
            ->willReturn([]);

        $searchCriteria = new SearchCriteria();
        $searchCriteria->addCriterion('p.is_template = 0');
        $paginator = new PaginationRequestHandler($mockRequest);

        $mockRepository = $this->createMock(TaskRepository::class);
        $mockRepository->expects($this->once())
            ->method('search')
            ->with($searchCriteria, $paginator)
            ->willReturn([]);

        $controller = new GetTasksController($mockRepository);
        $response = $controller($mockRequest);

        $this->assertEquals([], $response);
    }
}
