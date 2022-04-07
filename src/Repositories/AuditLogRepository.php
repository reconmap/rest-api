<?php declare(strict_types=1);

namespace Reconmap\Repositories;

use Ponup\SqlBuilders\SelectQueryBuilder;

class AuditLogRepository extends MysqlRepository
{
    public function findAll(int $page = 0, int $limitPerPage = 20): array
    {
        $queryBuilder = $this->getBaseSelectQueryBuilder();
        $queryBuilder->setLimit('?, ?');

        $limitOffset = $page * $limitPerPage;

        $stmt = $this->db->prepare($queryBuilder->toSql());
        $stmt->bind_param('ii', $limitOffset, $limitPerPage);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function countAll(): int
    {
        $sql = <<<SQL
        SELECT COUNT(*) AS total
        FROM audit_log al
        INNER JOIN user u ON (u.id = al.user_id)
        SQL;

        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        return (int)$row['total'];
    }

    public function findByUserId(int $userId): array
    {
        $queryBuilder = $this->getBaseSelectQueryBuilder();
        $queryBuilder->setWhere('al.user_id = ?');
        $queryBuilder->setLimit('10');

        $stmt = $this->db->prepare($queryBuilder->toSql());
        $stmt->bind_param('i', $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function findCountByDayStats(): array
    {
        $sql = <<<SQL
        SELECT DATE(insert_ts) AS log_date, COUNT(*) AS total
        FROM audit_log
        GROUP BY log_date
        ORDER BY log_date
        SQL;

        $rs = $this->db->query($sql);
        return $rs->fetch_all(MYSQLI_ASSOC);
    }

    public function insert(?int $userId, ?string $userAgent, string $clientIp, string $action, ?string $object = null): int
    {
        $stmt = $this->db->prepare('INSERT INTO audit_log (user_id, user_agent, client_ip, action, object) VALUES (?, ?, INET_ATON(?), ?, ?)');
        $stmt->bind_param('issss', $userId, $userAgent, $clientIp, $action, $object);
        return $this->executeInsertStatement($stmt);
    }

    private function getBaseSelectQueryBuilder(): SelectQueryBuilder
    {
        $queryBuilder = new SelectQueryBuilder('audit_log al');
        $queryBuilder->setColumns('al.id, al.insert_ts, al.user_agent, INET_NTOA(al.client_ip) AS client_ip, al.action, al.object,
               u.id AS user_id, u.username AS user_name, COALESCE(u.role, \'system\') AS user_role');
        $queryBuilder->addJoin('LEFT JOIN user u ON (u.id = al.user_id)');
        $queryBuilder->setOrderBy('al.insert_ts DESC');
        return $queryBuilder;
    }
}
