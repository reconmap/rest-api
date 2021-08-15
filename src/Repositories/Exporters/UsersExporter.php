<?php declare(strict_types=1);

namespace Reconmap\Repositories\Exporters;

use Reconmap\Repositories\UserRepository;

class UsersExporter implements Exportable
{
    public function __construct(private UserRepository $repository)
    {
    }

    public function export(string $entityType): array
    {
        return $this->repository->findAll();
    }
}
