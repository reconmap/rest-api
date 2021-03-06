<?php declare(strict_types=1);

namespace Reconmap\Repositories\Exporters;

use Reconmap\Repositories\UserRepository;

class UsersExporter
{
    public function __construct(private UserRepository $repository)
    {
    }

    public function export(): array
    {
        return $this->repository->findAll();
    }
}
