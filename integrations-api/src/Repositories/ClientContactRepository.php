<?php declare(strict_types=1);

namespace Reconmap\Repositories;

class ClientContactRepository extends MysqlRepository
{
    public function create(int $clientId, int $contactId): int
    {
        $stmt = $this->mysqlServer->prepare('INSERT INTO client_contact (client_id, contact_id) VALUES (?, ?)');
        $stmt->bind_param('ii', $clientId, $contactId);
        return $this->executeInsertStatement($stmt);
    }

    public function deleteById(int $id): bool
    {
        $stmt = $this->mysqlServer->prepare('DELETE FROM client_contact WHERE id = ?');
        $stmt->bind_param('i', $id);
        $result = $stmt->execute();
        $success = $result && 1 === $stmt->affected_rows;
        $stmt->close();

        return $success;
    }
}
