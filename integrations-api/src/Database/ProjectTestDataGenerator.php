<?php declare(strict_types=1);

namespace Reconmap\Database;

use Reconmap\Models\Project;
use Reconmap\Repositories\ProjectRepository;

class ProjectTestDataGenerator
{
    public function __construct(private readonly ProjectRepository $projectRepository)
    {

    }

    public function run(): void
    {
        $project = new Project();
        $project->id = 5;
        $project->creator_uid = 1;
        $project->client_id = null;
        $project->name = 'Linux host template';
        $project->description = 'Project template to show general linux host reconnaissance tasks';
        $project->is_template = true;
        $project->visibility = 'public';
        $project->external_id = null;
        $this->projectRepository->insert($project);

        $project = new Project();
        $project->id = 2;
        $project->creator_uid = 1;
        $project->client_id = 1;
        $project->name = 'Web server pentest project';
        $project->description = 'Test project to show pentest tasks and reports';
        $project->is_template = false;
        $project->visibility = 'private';
        $project->external_id = 'C8D6355A-5F54-43FC-A947-C4C960CDD4F6';
        $this->projectRepository->insert($project);

        $project = new Project();
        $project->id = 3;
        $project->creator_uid = 1;
        $project->client_id = 2;
        $project->name = 'Juice Shop (test project)';
        $project->description = 'OWASP Juice Shop is probably the most modern and sophisticated insecure web application! It can be used in security trainings,
awareness demos,
CTFs and as a guinea pig for security tools! Juice Shop encompasses vulnerabilities from the entire OWASP Top Ten along with many other security flaws found in real -world applications!';
        $project->is_template = false;
        $project->visibility = 'public';
        $project->external_id = null;
        $this->projectRepository->insert($project);

        $project = new Project();
        $project->id = 4;
        $project->creator_uid = 1;
        $project->client_id = 2;
        $project->name = 'WebGoat (test project)';
        $project->description = 'WebGoat is a deliberately insecure application that allows interested developers just like you to test vulnerabilities commonly found in Java-based applications that use common and popular open source components.';
        $project->is_template = false;
        $project->visibility = 'private';
        $project->external_id = null;
        $this->projectRepository->insert($project);
    }
}
