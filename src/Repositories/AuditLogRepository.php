<?php

declare(strict_types=1);

namespace Reconmap\Repositories;

class AuditLogRepository extends MysqlRepository
{
    public function findAll(int $page = 0): array
    {
        $sql = <<<SQL
        SELECT al.insert_ts, INET_NTOA(al.client_ip) AS client_ip, al.action, al.object,
        u.id AS user_id,
        u.name AS user_name,
        COALESCE(u.role, 'system') AS user_role
        FROM audit_log al
        LEFT JOIN user u ON (u.id = al.user_id)
        ORDER BY al.insert_ts DESC
        LIMIT ?, ?
        SQL;

        $limitPerPage = 20;
        $limitOffset = $page * $limitPerPage;

        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('ii', $limitOffset, $limitPerPage);
        $stmt->execute();
        $rs = $stmt->get_result();
        return $rs->fetch_all(MYSQLI_ASSOC);
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
        $rs = $stmt->get_result();
        $row = $rs->fetch_assoc();
        return (int)$row['total'];
    }

    public function findByUserId(int $userId): array
    {
        $sql = <<<SQL
        SELECT al.insert_ts, INET_NTOA(al.client_ip) AS client_ip, al.action, u.id AS user_id, u.name, u.role
        FROM audit_log al
        INNER JOIN user u ON (u.id = al.user_id)
        WHERE al.user_id = ?
        ORDER BY al.insert_ts DESC
        SQL;

        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('i', $userId);
        $stmt->execute();
        $rs = $stmt->get_result();
        return $rs->fetch_all(MYSQLI_ASSOC);
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

    public function insert(int $userId, string $clientIp, string $action, ?string $object = null): int
    {
        $stmt = $this->db->prepare('INSERT INTO audit_log (user_id, client_ip, action, object) VALUES (?, INET_ATON(?), ?, ?)');
        $stmt->bind_param('isss', $userId, $clientIp, $action, $object);
        return $this->executeInsertStatement($stmt);
    }
}
