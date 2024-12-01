<?php declare(strict_types=1);

namespace Reconmap\Repositories\Importers;

class Importables
{
    public const array List = [
        'projects' => ProjectsImporter::class,
        'project_templates' => ProjectsImporter::class,
        'commands' => CommandsImporter::class,
        'documents' => DocumentsImporter::class,
        'targets' => TargetsImporter::class,
        'tasks' => TasksImporter::class,
        'vulnerabilities' => VulnerabilitiesImporter::class,
        'vulnerability_templates' => VulnerabilityTemplatesImporter::class,
    ];
}
