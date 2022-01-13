<?php declare(strict_types=1);

namespace Reconmap\Models\AuditActions;

interface DocumentAuditActions
{
    public const CREATED = 'Document created';
    public const MODIFIED = 'Document modified';
    public const DELETED = 'Document deleted';
}
