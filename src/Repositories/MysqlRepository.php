<?php

declare(strict_types=1);

namespace Reconmap\Repositories;

abstract class MysqlRepository
{
    protected \mysqli $db;

    public function __construct(\mysqli $db)
    {
        $this->db = $db;
    }

    public function executeInsertStatement(\mysqli_stmt $stmt): int
    {
        $stmt->execute();
        $newId = $stmt->insert_id;
        $stmt->close();

        return $newId;
    }
}
