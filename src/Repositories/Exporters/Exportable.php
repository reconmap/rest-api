<?php declare(strict_types=1);

namespace Reconmap\Repositories\Exporters;

interface Exportable
{
    public function export(): array;
}
