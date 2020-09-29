<?php

declare(strict_types=1);

namespace Reconmap\Repositories;

class ClientRepository extends MysqlRepository
{
    public function findAll(): array
    {
        $rs = $this->db->query('SELECT * FROM client LIMIT 20');
        return $rs->fetch_all(MYSQLI_ASSOC);
    }

    public function findById(int $id): array
    {
        $stmt = $this->db->prepare('SELECT * FROM client WHERE id = ?');
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $rs = $stmt->get_result();
        $target = $rs->fetch_assoc();
        $stmt->close();

        return $target;
    }

    public function deleteById(int $id): bool
    {
        $stmt = $this->db->prepare('DELETE FROM client WHERE id = ?');
        $stmt->bind_param('i', $id);
        $result = $stmt->execute();
        $success = $result && 1 === $stmt->affected_rows;
        $stmt->close();

        return $success;
    }

    public function insert(object $client): int
    {
        $stmt = $this->db->prepare('INSERT INTO client (name, url, contact_name, contact_email, contact_phone) VALUES (?, ?, ?, ?, ?)');
        $stmt->bind_param('sssss', $client->name, $client->url, $client->contactName, $client->contactEmail, $client->contactPhone);
        return $this->executeInsertStatement($stmt);
    }
}
