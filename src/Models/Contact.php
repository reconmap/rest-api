<?php declare(strict_types=1);

namespace Reconmap\Models;

class Contact
{
    public ?int $id = null;
    public string $kind = 'general'; // general, technical, billing
    public ?string $name = null;
    public ?string $email = null;
    public ?string $phone = null;
    public ?string $role = null;
}

