<?php
declare(strict_types=1);

namespace Reconmap\Services;

class Environment
{
    public function getValue(string $name): ?string
    {
        $value = getenv($name);
        return $value !== false ? $value : null;
    }
}
