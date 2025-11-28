<?php declare(strict_types=1);

namespace Reconmap\Models\AuditActions;

enum AuditActions: string
{
    case CREATED = 'Created';
    case READ = 'Read';
    case UPDATED = 'Updated';
    case DELETED = 'Deleted';
}
