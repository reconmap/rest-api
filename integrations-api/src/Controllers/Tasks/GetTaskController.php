<?php declare(strict_types=1);

namespace Reconmap\Controllers\Tasks;

use OpenApi\Attributes as OpenApi;
use Reconmap\Controllers\GetEntityController;
use Reconmap\Http\Docs\Default200OkResponse;
use Reconmap\Http\Docs\Default403UnauthorisedResponse;
use Reconmap\Http\Docs\InPathIdParameter;
use Reconmap\Repositories\TaskRepository;

#[OpenApi\Get(
    path: "/tasks/{taskId}",
    description: "Returns information about the task with the given id",
    security: ["bearerAuth"],
    tags: ["Tasks"],
    parameters: [new InPathIdParameter("taskId")])
]
#[Default200OkResponse]
#[Default403UnauthorisedResponse]
class GetTaskController extends GetEntityController
{
    public function __construct(TaskRepository $repository)
    {
        parent::__construct($repository, 'taskId');
    }
}
