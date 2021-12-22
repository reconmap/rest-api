<?php declare(strict_types=1);

namespace Reconmap\Models;

class Target
{
    public int $project_id;
    public ?int $parent_id = null;
    public string $name;
    public string $kind;
    public ?string $tags;

    public function hasParent(): bool
    {
        return !is_null($this->parent_id);
    }
}
