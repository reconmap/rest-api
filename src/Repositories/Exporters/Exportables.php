<?php declare(strict_types=1);

namespace Reconmap\Repositories\Exporters;

class Exportables
{
    public const array List = [
        ['key' => 'audit_log', 'description' => 'Audit log', 'className' => AuditLogExporter::class],
        ['key' => 'clients', 'description' => 'Clients', 'className' => ClientsExporter::class],
        ['key' => 'commands', 'description' => 'Commands', 'className' => CommandsExporter::class],
        ['key' => 'documents', 'description' => 'Documents', 'className' => DocumentsExporter::class],
        ['key' => 'projects', 'description' => 'Projects', 'className' => ProjectsExporter::class],
        ['key' => 'project_templates', 'description' => 'Project templates', 'className' => ProjectTemplatesExporter::class],
        ['key' => 'tasks', 'description' => 'Tasks', 'className' => TasksExporter::class],
        ['key' => 'targets', 'description' => 'Targets', 'className' => TargetsExporter::class],
        ['key' => 'users', 'description' => 'Users', 'className' => UsersExporter::class],
        ['key' => 'vulnerabilities', 'description' => 'Vulnerabilities', 'className' => VulnerabilitiesExporter::class],
        ['key' => 'vulnerability_category', 'description' => 'Vulnerability categories', 'className' => VulnerabilityCategoriesExporter::class],
        ['key' => 'vulnerability_templates', 'description' => 'Vulnerability templates', 'className' => VulnerabilityTemplatesExporter::class],
    ];
}
