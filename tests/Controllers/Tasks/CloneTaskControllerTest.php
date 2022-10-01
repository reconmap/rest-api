<?php declare(strict_types=1);

namespace Reconmap\Controllers\Tasks;

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Reconmap\Http\ApplicationRequest;
use Reconmap\Models\User;
use Reconmap\Repositories\TaskRepository;

class CloneTaskControllerTest extends TestCase
{
    public function testProcess()
    {
        $fakeUser = new User();
        $fakeUser->id = 5;

        $fakeTaskId = 1;

        $mockTaskRepository = $this->createMock(TaskRepository::class);
        $mockTaskRepository->expects($this->once())
            ->method('clone')
            ->with($fakeTaskId, $fakeUser->id);
        $appRequest = $this->createMock(ApplicationRequest::class);
        $appRequest->expects($this->once())
            ->method('getArgs')
            ->willReturn(['taskId' => 1]);
        $appRequest->expects($this->once())
            ->method('getUser')
            ->willReturn($fakeUser);

        $controller = new class($mockTaskRepository) extends CloneTaskController {
            public function processFromTest(ApplicationRequest $request): ResponseInterface
            {
                return $this->process($request);
            }
        };
        $controller->processFromTest($appRequest);
    }
}
