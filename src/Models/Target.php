<?php declare(strict_types=1);

namespace Reconmap\Models;

class Target
{
    public int $projectId;
    public string $name;
    public string $kind;
    public ?string $tags;
}
