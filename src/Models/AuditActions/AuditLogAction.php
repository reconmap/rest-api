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
    public const string DATA_IMPORTED = 'Imported data';
    public const string DATA_EXPORTED = 'Exported data';

    public const string ORGANISATION_UPDATED = 'Updated organisation';
}
