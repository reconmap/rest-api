<?php declare(strict_types=1);

namespace Reconmap\Repositories\Exporters;

use Reconmap\Repositories\VulnerabilityRepository;

class VulnerabilitiesExporter
{
    public function __construct(private VulnerabilityRepository $repository)
    {
    }

    public function export(): array
    {
        return $this->repository->findAll();
    }
}
