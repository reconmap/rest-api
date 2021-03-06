<?php declare(strict_types=1);

namespace Reconmap\Repositories\Exporters;

use Reconmap\Repositories\ProjectRepository;

class ProjectsExporter
{
    public function __construct(private ProjectRepository $repository)
    {
    }

    public function export(): array
    {
        return $this->repository->findAll();
    }
}
