<?php declare(strict_types=1);

namespace Reconmap\Models\AuditActions;

interface ProjectLogActions
{
    public const PROJECT_CREATED = 'Created project';
    public const PROJECT_MODIFIED = 'Modified project';
    public const PROJECT_DELETED = 'Deleted project';

}
