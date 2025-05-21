<?php declare(strict_types=1);

namespace Reconmap\Models\AuditActions;

enum UserAuditActions: string
{
    case LOGGED_IN = 'Logged in';
    case LOGGED_OUT = 'Logged out';
    case REQUESTED_ACTION = 'Requested action';
}
