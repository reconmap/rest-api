<?php declare(strict_types=1);

namespace Reconmap\Repositories\Importers;

use Reconmap\Models\Target;
use Reconmap\Models\Task;
use Reconmap\Repositories\TaskRepository;
use Reconmap\Services\ObjectCaster;

class TasksImporter implements Importable
{
    public function __construct(private readonly TaskRepository $repository)
    {
    }

    /**
     * @param int $userId
     * @param array<Target> $tasks
     * @return array
     */
    public function import(int $userId, array $tasks): array
    {
        $response = [
            'count' => 0,
            'errors' => [],
        ];

        foreach ($tasks as $jsonTask) {
            try {
                /** @var Task $jsonTask */
                $jsonTask = ObjectCaster::cast(new Task(), $jsonTask);
                $this->repository->insert($jsonTask);

                $response['count']++;
            } catch (\Exception $e) {
                $response['errors'][] = $e->getMessage();
            }
        }

        return $response;
    }
}
