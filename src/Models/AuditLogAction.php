<?php

declare(strict_types=1);

namespace Reconmap\Models;

class AuditLogAction
{

    // User related actions
    public const USER_LOGGED_IN = 'User logged in';
    public const USER_LOGGED_OUT = 'User logged out';
    public const USER_CREATED = 'User created';
    public const USER_MODIFIED = 'User modified';
    public const USER_DELETED = 'User deleted';

    // Integration related actions
    public const INTEGRATION_ENABLED = 'Integration enabled';
    public const INTEGRATION_DISABLED = 'Integration disabled';
}
