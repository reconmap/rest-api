<?php declare(strict_types=1);

namespace Reconmap\Repositories;

use Reconmap\Models\Client;

class ClientRepository extends MysqlRepository implements Updateable, Findable
{
    public const array UPDATABLE_COLUMNS_TYPES = [
        'kind' => 's',
        'name' => 's',
        'address' => 's',
        'url' => 's',
        'contact_name' => 's',
        'contact_email' => 's',
        'contact_phone' => 's',
        'logo_attachment_id' => 'i',
        'small_logo_attachment_id' => 'i',
    ];

    public function findAll(): array
    {
        $sql = <<<SQL
SELECT *, (SELECT COUNT(*) FROM client_contact WHERE client_id = client.id) AS num_contacts
FROM client
ORDER BY name ASC
SQL;

        $result = $this->mysqlServer->query($sql);
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

        $stmt = $this->mysqlServer->prepare($sql);
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $client = $result->fetch_object(\Reconmap\DomainObjects\Client::class);
        $stmt->close();

        return $client;
    }

    public function deleteById(int $id): bool
    {
        return $this->deleteByTableId('client', $id);
    }

    public function insert(Client $client): int
    {
        $stmt = $this->mysqlServer->prepare('INSERT INTO client (kind, creator_uid, name, address, url, logo_attachment_id, small_logo_attachment_id) VALUES (?, ?, ?, ?, ?, ?, ?)');
        if (false === $stmt->execute([$client->kind, $client->creator_uid, $client->name, $client->address, $client->url, $client->logo_attachment_id, $client->small_logo_attachment_id])) {
            throw new \Exception('Failed to insert organisation record');
        }
        return $stmt->insert_id;
    }

    public function updateById(int $id, array $newColumnValues): bool
    {
        return $this->updateByTableId('client', $id, $newColumnValues);
    }
}
