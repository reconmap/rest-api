<?php declare(strict_types=1);

namespace Reconmap\Processors;

interface HostParser
{
    public function parseHost(string $path): array;
}
