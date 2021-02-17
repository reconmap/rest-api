<?php declare(strict_types=1);

namespace Reconmap\Models;

class AuditLogAction implements
    UserAuditActions,
    ProjectLogActions,
    VulnerabilityLogActions,
    ClientAuditActions,
    TaskAuditActions,
    TargetAuditActions
{
    public const AUDIT_LOG_EXPORTED = 'Exported audit log';

    public const INTEGRATION_ENABLED = 'Enabled integration';
    public const INTEGRATION_DISABLED = 'Disabled integration';

    public const COMMAND_UPDATED = 'Updated command';

    public const DATA_IMPORTED = 'Imported data';
    public const DATA_EXPORTED = 'Exported data';

    public const ORGANISATION_UPDATED = 'Updated organisation';

    public const COMMAND_OUTPUT_DELETED = 'Deleted command output';

    public const ATTACHMENT_DOWNLOADED = 'Downloaded attachment';
}
