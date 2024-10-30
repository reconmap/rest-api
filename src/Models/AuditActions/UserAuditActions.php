<?php declare(strict_types=1);

namespace Reconmap\Models\AuditActions;

interface UserAuditActions
{
    public const string USER_LOGGED_IN = 'Logged in';
    public const string USER_LOGGED_OUT = 'Logged out';
    public const string USER_CREATED = 'Created user';
    public const string USER_MODIFIED = 'Modified user';
    public const string USER_DELETED = 'Deleted user';

    public const string USER_REQUESTED_ACTION = 'Requested action';
}
