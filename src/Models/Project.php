<?php

declare(strict_types=1);

namespace Reconmap\Models;

class Project
{
    public ?int $clientId;
    public string $name;
    public ?string $description;
    public bool $isTemplate = false;
}
