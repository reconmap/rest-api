<?php declare(strict_types=1);

namespace Reconmap\Repositories;

use Ponup\SqlBuilders\SelectQueryBuilder;

class ProjectCategoriesRepository extends MysqlRepository
{
    public function findAll(): array
    {
        $queryBuilder = $this->getBaseSelectQueryBuilder();
        $resultSet = $this->db->query($queryBuilder->toSql());
        return $resultSet->fetch_all(MYSQLI_ASSOC);
    }

    protected function getBaseSelectQueryBuilder(): SelectQueryBuilder
    {
        $queryBuilder = new SelectQueryBuilder('project_category pc');
        $queryBuilder->setColumns('pc.*');
        $queryBuilder->setOrderBy('name ASC');
        return $queryBuilder;
    }
}
