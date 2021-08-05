<?php declare(strict_types=1);

namespace Reconmap\Controllers\Tasks;

use Reconmap\Controllers\GetEntityController;
use Reconmap\Repositories\TaskRepository;

class GetTaskController extends GetEntityController
{
    public function __construct(TaskRepository $repository)
    {
        parent::__construct($repository, 'taskId');
    }
}
