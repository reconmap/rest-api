<?php declare(strict_types=1);

namespace Reconmap\Repositories\Exporters;

use Reconmap\Repositories\CommandRepository;

class CommandsExporter implements Exportable
{
    public function __construct(private CommandRepository $repository)
    {
    }

    public function export(): array
    {
        return $this->repository->findAll();
    }
}
