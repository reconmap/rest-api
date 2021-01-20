<?php declare(strict_types=1);

namespace Reconmap\Repositories;

class CommandRepository extends MysqlRepository
{
    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM command WHERE id = ?');
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $rs = $stmt->get_result();
        $command = $rs->fetch_assoc();
        $stmt->close();

        return $command;
    }
}
