<?php
declare(strict_types=1);

namespace Reconmap\Models;


class Client
{
    public ?int $id;
    public ?string $name;
    public ?string $url;
    public ?string $contactName;
    public ?string $contactEmail;
    public ?string $contactPhone;
}