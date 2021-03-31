<?php declare(strict_types=1);

namespace Reconmap\Models;

class User
{
    public bool $active = true;
    public string $full_name;
    public ?string $short_bio;
    public string $username;
    public string $password;
    public string $email;
    public string $role;
}
