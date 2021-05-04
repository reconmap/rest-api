<?php declare(strict_types=1);

namespace Reconmap\Controllers\Tasks;

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Repositories\TaskRepository;

class GetTasksControllerTest extends TestCase
{
    public function testHappyPath()
    {
        $mockRequest = $this->createMock(ServerRequestInterface::class);

        $mockRepository = $this->createMock(TaskRepository::class);
        $mockRepository->expects($this->once())
            ->method('findAll')
            ->willReturn([]);

        $controller = new GetTasksController($mockRepository);
        $response = $controller($mockRequest);

        $this->assertEquals([], $response);
    }
}
