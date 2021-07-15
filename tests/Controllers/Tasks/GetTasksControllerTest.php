<?php declare(strict_types=1);

namespace Reconmap\Controllers\Tasks;

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Repositories\QueryBuilders\SearchCriteria;
use Reconmap\Repositories\TaskRepository;
use Reconmap\Services\RequestPaginator;

class GetTasksControllerTest extends TestCase
{
    public function testHappyPath()
    {
        $mockRequest = $this->createMock(ServerRequestInterface::class);
        $mockRequest->expects($this->exactly(3))
            ->method('getQueryParams')
            ->willReturn([]);

        $searchCriteria = new SearchCriteria();
        $paginator = new RequestPaginator($mockRequest);

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
