<?php declare(strict_types=1);

namespace Reconmap\Repositories;

use Reconmap\Models\Client;
use Reconmap\Repositories\QueryBuilders\UpdateQueryBuilder;

class ClientRepository extends MysqlRepository
{
    public const UPDATABLE_COLUMNS_TYPES = [
        'name' => 's',
        'url' => 's',
        'contact_name' => 's',
        'contact_email' => 's',
        'contact_phone' => 's',
    ];

    public function findAll(): array
    {
        $rs = $this->db->query('SELECT * FROM client LIMIT 20');
        return $rs->fetch_all(MYSQLI_ASSOC);
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
        $rs = $stmt->get_result();
        $client = $rs->fetch_object(Client::class);
        $stmt->close();

        return $client;
    }

    public function deleteById(int $id): bool
    {
        return $this->deleteByTableId('client', $id);
    }

    public function insert(Client $client): int
    {
        $stmt = $this->db->prepare('INSERT INTO client (creator_uid, name, url, contact_name, contact_email, contact_phone) VALUES (?, ?, ?, ?, ?, ?)');
        $stmt->bind_param('isssss', $client->creator_uid, $client->name, $client->url, $client->contact_name, $client->contact_email, $client->contact_phone);
        return $this->executeInsertStatement($stmt);
    }

    public function updateById(int $id, array $newColumnValues): bool
    {
        $updateQueryBuilder = new UpdateQueryBuilder('client');
        $updateQueryBuilder->setColumnValues(array_map(fn() => '?', $newColumnValues));
        $updateQueryBuilder->setWhereConditions('id = ?');

        $stmt = $this->db->prepare($updateQueryBuilder->toSql());
        call_user_func_array([$stmt, 'bind_param'], [$this->generateParamTypes(array_keys($newColumnValues)) . 'i', ...$this->refValues($newColumnValues), &$id]);
        $result = $stmt->execute();
        $success = $result && 1 === $stmt->affected_rows;
        $stmt->close();

        return $success;
    }
}
