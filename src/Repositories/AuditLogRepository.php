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
		SELECT al.*, u.*
		FROM audit_log al
		INNER JOIN user u ON (u.id = al.user_id)
		ORDER BY al.insert_ts DESC
		SQL;

		$rs = $this->db->query($sql);
		$rows = $rs->fetch_all(MYSQLI_ASSOC);
        return $rows;
    }
}
