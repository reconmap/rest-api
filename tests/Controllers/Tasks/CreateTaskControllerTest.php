<?php declare(strict_types=1);

namespace Reconmap\Controllers\Tasks;

use GuzzleHttp\Psr7\Utils;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Models\Task;
use Reconmap\Repositories\TaskRepository;

class CreateTaskControllerTest extends TestCase
{
    public function testHappyPath()
    {
        $mockRequest = $this->createMock(ServerRequestInterface::class);
        $mockRequest->expects($this->once())
            ->method('getBody')
            ->willReturn(Utils::streamFor('{}'));
        $mockRequest->expects($this->once())
            ->method('getAttribute')
            ->with('userId')
            ->willReturn(9);

        $expectedTaskToBeInserted = new Task();
        $expectedTaskToBeInserted->creator_uid = 9;

        $mockTaskRepository = $this->createMock(TaskRepository::class);
        $mockTaskRepository->expects($this->once())
            ->method('insert')
            ->with($expectedTaskToBeInserted)
            ->willReturn(1);

        $controller = new CreateTaskController($mockTaskRepository);
        $response = $controller($mockRequest);

        $this->assertTrue($response['success']);
    }
}
