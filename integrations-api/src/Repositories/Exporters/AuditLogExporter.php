<?php declare(strict_types=1);

namespace Reconmap\Repositories\Exporters;

use Reconmap\Repositories\AuditLogRepository;

class AuditLogExporter implements Exportable
{
    public function __construct(private AuditLogRepository $repository)
    {
    }

    public function export(): array
    {
        return $this->repository->findAll();
    }
}
