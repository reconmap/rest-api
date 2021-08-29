<?php declare(strict_types=1);

namespace Reconmap\Repositories;

use Reconmap\Repositories\QueryBuilders\SelectQueryBuilder;

class ProjectTemplateRepository extends ProjectRepository
{
    protected function getBaseSelectQueryBuilder(): SelectQueryBuilder
    {
        $queryBuilder = new SelectQueryBuilder('project_template p');
        $queryBuilder->setColumns('
            p.*,
            (SELECT COUNT(*) FROM task WHERE project_id = p.id) AS num_tasks
        ');
        return $queryBuilder;
    }
}
