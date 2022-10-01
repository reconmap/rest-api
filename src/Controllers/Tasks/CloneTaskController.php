<?php declare(strict_types=1);

namespace Reconmap\Controllers\Tasks;

use Psr\Http\Message\ResponseInterface;
use Reconmap\Controllers\ControllerV2;
use Reconmap\Http\ApplicationRequest;
use Reconmap\Repositories\TaskRepository;

class CloneTaskController extends ControllerV2
{
    public function __construct(private readonly TaskRepository $taskRepository)
    {
    }

    protected function process(ApplicationRequest $request): ResponseInterface
    {
        $taskId = intval($request->getArgs()['taskId']);

        $task = $this->taskRepository->clone($taskId, $request->getUser()->id);

        return $this->createStatusCreatedResponse($task);
    }
}
