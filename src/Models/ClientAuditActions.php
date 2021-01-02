<?php declare(strict_types=1);

namespace Reconmap\Models;

interface ClientAuditActions
{
    public const CLIENT_CREATED = 'Created client';
    public const CLIENT_MODIFIED = 'Modified client';
    public const CLIENT_DELETED = 'Deleted client';
}
