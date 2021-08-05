<?php declare(strict_types=1);

namespace Reconmap\Repositories;

interface Findable
{
    public function findById(int $id): array|object|null;
}
