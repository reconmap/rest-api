<?php declare(strict_types=1);

namespace Reconmap\Controllers\Tasks;

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Repositories\SearchCriterias\TaskSearchCriteria;
use Reconmap\Repositories\TaskRepository;
use Reconmap\Services\PaginationRequestHandler;

class GetTasksControllerTest extends TestCase
{
    public function testHappyPath()
    {
        $mockRequest = $this->createMock(ServerRequestInterface::class);
        $mockRequest->expects($this->exactly(2))
            ->method('getAttribute')
            ->withConsecutive(['userId'], ['role'])
            ->willReturnOnConsecutiveCalls(9, 'superuser');
        $mockRequest->expects($this->exactly(3))
            ->method('getQueryParams')
            ->willReturn([]);

        $mockSearchCriteria = $this->createMock(TaskSearchCriteria::class);
        $mockSearchCriteria->expects($this->once())
            ->method('addIsNotTemplateCriterion');

        $paginator = new PaginationRequestHandler($mockRequest);

        $mockRepository = $this->createMock(TaskRepository::class);
        $mockRepository->expects($this->once())
            ->method('search')
            ->with($mockSearchCriteria, $paginator)
            ->willReturn([]);


        $controller = new GetTasksController($mockRepository, $mockSearchCriteria);
        $response = $controller($mockRequest);

        $this->assertEquals([], $response);
    }
}
