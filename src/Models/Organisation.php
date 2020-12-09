<?php declare(strict_types=1);

namespace Reconmap\Models;

class Organisation
{
    public ?int $id = null;
    public string $name;
    public ?string $url = null;
    public ?string $contactName = null;
    public ?string $contactEmail = null;
    public ?string $contactPhone = null;
}
