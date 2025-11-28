<?php declare(strict_types=1);

namespace Reconmap\Models\AuditActions;

enum SystemAuditActions: string
{
    case INITIALISE = 'Initialised';
    case UPGRADE = 'Upgraded';
    case TEST = 'Tested';
}
