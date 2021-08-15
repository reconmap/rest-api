<?php declare(strict_types=1);

namespace Reconmap\Repositories\Exporters;

use Reconmap\Repositories\TaskRepository;

class TasksExporter implements Exportable
{
    public function __construct(private TaskRepository $repository)
    {
    }

    public function export(string $entityType): array
    {
        return $this->repository->findAll(false, null);
    }
}
