<?php declare(strict_types=1);

namespace Reconmap\Repositories\Exporters;

use Reconmap\Repositories\ClientRepository;

class ClientsExporter
{
    public function __construct(private ClientRepository $repository)
    {
    }

    public function export(): array
    {
        return $this->repository->findAll();
    }
}
