<?php declare(strict_types=1);

namespace Reconmap\Models\AuditActions;

interface ClientAuditActions
{
    public const CREATED = 'Created client';
    public const UPDATED = 'Updated client';
    public const DELETED = 'Deleted client';
}
