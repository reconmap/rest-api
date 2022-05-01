<?php declare(strict_types=1);

namespace Reconmap\Models\AuditActions;

use Reconmap\Models\VulnerabilityCategoryLogActions;

class AuditLogAction implements
    AttachmentAuditActions,
    ClientAuditActions,
    ProjectLogActions,
    TaskAuditActions,
    TargetAuditActions,
    UserAuditActions,
    VulnerabilityLogActions,
    VulnerabilityCategoryLogActions
{
    public const DATA_IMPORTED = 'Imported data';
    public const DATA_EXPORTED = 'Exported data';

    public const ORGANISATION_UPDATED = 'Updated organisation';
}
