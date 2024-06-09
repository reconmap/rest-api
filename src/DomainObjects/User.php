<?php declare(strict_types=1);

namespace Reconmap\DomainObjects;

class User extends \Reconmap\Models\User
{
    public function isAdministrator(): bool
    {
        return 'administrator' === $this->role;
    }
}
