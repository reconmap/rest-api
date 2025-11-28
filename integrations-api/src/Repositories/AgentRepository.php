<?php declare(strict_types=1);

namespace Reconmap\Repositories;

use Ponup\SqlBuilders\SelectQueryBuilder;

class AgentRepository extends MysqlRepository
{
    public const array UPDATABLE_COLUMNS_TYPES = [
        'version' => 's',
        'hostname' => 's',
        'arch' => 's',
        'cpu' => 's',
        'memory' => 's',
        'os' => 's',
        'active' => 'i',
        'last_boot_at' => 's',
        'last_ping_at' => 's',
    ];

    public function findAll(): array
    {
        $queryBuilder = $this->getBaseSelectQueryBuilder();
        $result = $this->mysqlServer->query($queryBuilder->toSql());
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function updateLastPingAt(): bool
    {
        return $this->updateByTableId('agent', 1, ['last_ping_at' => date('Y-m-d H:i:s')]);
    }

    public function updateLastBootAt(object $info): bool
    {
        $currentDateTime = date('Y-m-d H:i:s');

        return $this->updateByTableId('agent', 1, [
            'version' => $info->version,
            'hostname' => $info->hostname,
            'arch' => $info->arch,
            'cpu' => $info->cpu,
            'memory' => $info->memory,
            'os' => $info->os,
            'last_boot_at' => $currentDateTime,
            'last_ping_at' => $currentDateTime
        ]);
    }

    protected function getBaseSelectQueryBuilder(): SelectQueryBuilder
    {
        $queryBuilder = new SelectQueryBuilder('agent a');
        $queryBuilder->setColumns('
            a.*
        ');
        $queryBuilder->setOrderBy('a.client_id ASC, a.last_ping_at DESC');
        return $queryBuilder;
    }
}
