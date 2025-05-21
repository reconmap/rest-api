<?php declare(strict_types=1);

namespace Reconmap\Models\AuditActions;

enum DataAuditActions: string
{
    case IMPORTED = 'Imported';
    case EXPORTED = 'Exported';
}
