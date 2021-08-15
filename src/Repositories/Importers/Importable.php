<?php declare(strict_types=1);

namespace Reconmap\Repositories\Importers;

interface Importable
{
    public function import(int $userId, array $entities): array;
}
