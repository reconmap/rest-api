<?php declare(strict_types=1);

namespace Reconmap\Models;

interface Cleanable
{
    public function clean(): void;
}
