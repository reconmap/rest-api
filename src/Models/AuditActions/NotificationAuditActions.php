<?php declare(strict_types=1);

namespace Reconmap\Models\AuditActions;

interface NotificationAuditActions
{
    public const CREATED = 'Created notification';
    public const UPDATED = 'Updated notification';
    public const DELETED = 'Deleted notification';
}

