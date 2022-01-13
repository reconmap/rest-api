<?php declare(strict_types=1);

namespace Reconmap\Models\AuditActions;

interface UserAuditActions
{
    public const USER_LOGIN_FAILED = 'Failed login';
    public const USER_LOGGED_IN = 'Logged in';
    public const USER_LOGGED_OUT = 'Logged out';
    public const USER_CREATED = 'Created user';
    public const USER_MODIFIED = 'Modified user';
    public const USER_DELETED = 'Deleted user';

    public const USER_PASSWORD_CHANGED = 'Changed own password';
}
