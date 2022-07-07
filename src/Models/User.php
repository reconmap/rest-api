<?php declare(strict_types=1);

namespace Reconmap\Models;

class User
{
    public ?int $id;
    public ?string $oidc_id;
    public bool $active = true;
    public string $full_name;
    public ?string $short_bio;
    public string $username;
    public string $email;
    public ?string $role = null;

    public function isAdministrator(): bool
    {
        return 'administrator' === $this->role;
    }
}
