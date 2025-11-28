<?php declare(strict_types=1);

namespace Reconmap\Repositories;

interface Updateable
{
    public function updateById(int $id, array $newColumnValues): bool;
}
