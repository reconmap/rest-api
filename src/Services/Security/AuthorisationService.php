<?php declare(strict_types=1);

namespace Reconmap\Services\Security;

class AuthorisationService
{
    public function isRoleAllowed(string $role, string $action): bool
    {
        $granted = Permissions::ByRoles[$role];
        if (in_array($action, $granted) || in_array('*.*', $granted)) {
            return true;
        }

        $parts = explode('.', $action);
        return in_array($parts[0] . '.*', $granted);
    }
}
