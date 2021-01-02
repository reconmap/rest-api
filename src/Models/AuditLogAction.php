<?php declare(strict_types=1);

namespace Reconmap\Models;

class AuditLogAction implements UserAuditActions, ProjectLogActions, VulnerabilityLogActions, ClientAuditActions
{
    public const AUDIT_LOG_EXPORTED = 'Exported audit log';

    public const TASK_DELETED = 'Deleted task';

    public const INTEGRATION_ENABLED = 'Enabled integration';
    public const INTEGRATION_DISABLED = 'Disabled integration';

    public const DATA_IMPORTED = 'Imported data';
    public const DATA_EXPORTED = 'Exported data';

    public const ORGANISATION_UPDATED = 'Updated organisation';
}
