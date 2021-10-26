<?php declare(strict_types=1);

namespace Reconmap\Models;

use Reconmap\Repositories\Exporters\AuditLogExporter;
use Reconmap\Repositories\Exporters\ClientsExporter;
use Reconmap\Repositories\Exporters\CommandsExporter;
use Reconmap\Repositories\Exporters\DocumentsExporter;
use Reconmap\Repositories\Exporters\ProjectsExporter;
use Reconmap\Repositories\Exporters\ProjectTemplatesExporter;
use Reconmap\Repositories\Exporters\TasksExporter;
use Reconmap\Repositories\Exporters\UsersExporter;
use Reconmap\Repositories\Exporters\VulnerabilitiesExporter;
use Reconmap\Repositories\Exporters\VulnerabilityCategoriesExporter;
use Reconmap\Repositories\Exporters\VulnerabilityTemplatesExporter;

class Exportables
{
    public const List = [
        ['key' => 'audit_log', 'description' => 'Audit log', 'className' => AuditLogExporter::class],
        ['key' => 'clients', 'description' => 'Clients', 'className' => ClientsExporter::class],
        ['key' => 'commands', 'description' => 'Commands', 'className' => CommandsExporter::class],
        ['key' => 'documents', 'description' => 'Documents', 'className' => DocumentsExporter::class],
        ['key' => 'projects', 'description' => 'Projects', 'className' => ProjectsExporter::class],
        ['key' => 'project_templates', 'description' => 'Project templates', 'className' => ProjectTemplatesExporter::class],
        ['key' => 'tasks', 'description' => 'Tasks', 'className' => TasksExporter::class],
        ['key' => 'users', 'description' => 'Users', 'className' => UsersExporter::class],
        ['key' => 'vulnerabilities', 'description' => 'Vulnerabilities', 'className' => VulnerabilitiesExporter::class],
        ['key' => 'vulnerability_category', 'description' => 'Vulnerability categories', 'className' => VulnerabilityCategoriesExporter::class],
        ['key' => 'vulnerability_templates', 'description' => 'Vulnerability templates', 'className' => VulnerabilityTemplatesExporter::class],
    ];
}
