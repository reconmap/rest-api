<?php declare(strict_types=1);

namespace Reconmap\Models\AuditActions;

interface AttachmentAuditActions
{
    public const ATTACHMENT_CREATED = 'Created attachment';
    public const ATTACHMENT_DELETED = 'Deleted attachment';
    public const ATTACHMENT_UPDATED = 'Updated attachment';
    public const ATTACHMENT_DOWNLOADED = 'Downloaded attachment';
}
