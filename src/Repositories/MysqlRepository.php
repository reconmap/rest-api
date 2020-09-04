<?php

declare(strict_types=1);

namespace Reconmap\Repositories;

abstract class MysqlRepository
{

    public function executeInsertStatement(\mysqli_stmt $stmt): int
    {
        $stmt->execute();
        $newId = $stmt->insert_id;
        $stmt->close();

        return $newId;
    }
}
