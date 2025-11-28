<?php declare(strict_types=1);

namespace Reconmap\Repositories\Exporters;

use Reconmap\Repositories\TargetRepository;

class TargetsExporter implements Exportable
{
    public function __construct(private TargetRepository $repository)
    {
    }

    public function export(): array
    {
        return $this->repository->findAll();
    }
}
