<?php declare(strict_types=1);

namespace Reconmap\Models\AuditActions;

interface ClientAuditActions
{
    public const string CREATED = 'Created client';
    public const string UPDATED = 'Updated client';
    public const string DELETED = 'Deleted client';
}
