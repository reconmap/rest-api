<?php

declare(strict_types=1);

namespace Reconmap\Repositories;

class AuditLogRepository
{

    private $db;

    public function __construct(\mysqli $db)
    {
        $this->db = $db;
    }

    public function findAll(): array
    {
        $sql = <<<SQL
		SELECT al.insert_ts, INET_NTOA(al.client_ip) AS client_ip, al.action, u.name, u.role
		FROM audit_log al
		INNER JOIN user u ON (u.id = al.user_id)
		ORDER BY al.insert_ts DESC
		SQL;

        $rs = $this->db->query($sql);
        $rows = $rs->fetch_all(MYSQLI_ASSOC);
        return $rows;
    }

    public function findCountByDayStats(): array
    {
        $sql = <<<SQL
        SELECT DATE(insert_ts) AS log_date, COUNT(*) AS total
        FROM audit_log
        GROUP BY log_date
        ORDER BY log_date ASC
        SQL;

        $rs = $this->db->query($sql);
        $rows = $rs->fetch_all(MYSQLI_ASSOC);
        return $rows;
    }

    public function insert(int $userId, string $clientIp, string $action): void
    {
        $stmt = $this->db->prepare('INSERT INTO audit_log (user_id, client_ip, action) VALUES (?, INET_ATON(?), ?)');
        $stmt->bind_param('iss', $userId, $clientIp, $action);
        if (false === $stmt->execute()) {
            throw new \Exception($stmt->error);
        }
        $stmt->close();
    }
}
