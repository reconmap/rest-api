<?php declare(strict_types=1);

namespace Reconmap\Controllers\Tasks;

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Repositories\TaskRepository;

class GetTaskControllerTest extends TestCase
{
    public function testHappyPath()
    {
        $mockTask = ['summary' => 'Hack all the things'];

        $mockRequest = $this->createMock(ServerRequestInterface::class);

        $mockRepository = $this->createMock(TaskRepository::class);
        $mockRepository->expects($this->once())
            ->method('findById')
            ->with(42)
            ->willReturn($mockTask);

        $args = ['taskId' => 42];

        $controller = new GetTaskController($mockRepository);
        $response = $controller($mockRequest, $args);

        $this->assertEquals($mockTask, $response);
    }
}
