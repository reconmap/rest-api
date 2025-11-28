<?php declare(strict_types=1);

namespace Reconmap\Repositories\Exporters;

use Reconmap\Repositories\ClientRepository;

class ClientsExporter implements Exportable
{
    public function __construct(private readonly ClientRepository $repository)
    {
    }

    public function export(): array
    {
        return $this->repository->findAll();
    }
}
