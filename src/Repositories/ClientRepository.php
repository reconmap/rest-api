<?php declare(strict_types=1);

namespace Reconmap\Repositories;

use Reconmap\Models\Client;

class ClientRepository extends MysqlRepository implements Updateable, Findable
{
    public const UPDATABLE_COLUMNS_TYPES = [
        'name' => 's',
        'address' => 's',
        'url' => 's',
        'contact_name' => 's',
        'contact_email' => 's',
        'contact_phone' => 's',
    ];

    public function findAll(): array
    {
        $sql = <<<SQL
SELECT *, (SELECT COUNT(*) FROM client_contact WHERE client_id = client.id) AS num_contacts
FROM client
ORDER BY name ASC
SQL;

        $result = $this->db->query($sql);
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function findById(int $id): ?Client
    {
        $sql = <<<SQL
SELECT
       c.*,
       u.full_name AS creator_full_name
FROM
    client c
    INNER JOIN user u ON (u.id = c.creator_uid)
WHERE c.id = ?
SQL;

        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $client = $result->fetch_object(Client::class);
        $stmt->close();

        return $client;
    }

    public function deleteById(int $id): bool
    {
        return $this->deleteByTableId('client', $id);
    }

    public function insert(Client $client): int
    {
        $stmt = $this->db->prepare('INSERT INTO client (creator_uid, name, address, url) VALUES (?, ?, ?, ?)');
        $stmt->bind_param('isss', $client->creator_uid, $client->name, $client->address, $client->url);
        return $this->executeInsertStatement($stmt);
    }

    public function updateById(int $id, array $newColumnValues): bool
    {
        return $this->updateByTableId('client', $id, $newColumnValues);
    }
}
