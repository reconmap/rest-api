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
        $stmt = $this->db->prepare('SELECT * FROM client WHERE id = ?');
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $rs = $stmt->get_result();
        $target = $rs->fetch_object(Client::class);
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
        $stmt->bind_param('sssss', $client->name, $client->url, $client->contact_name, $client->contact_email, $client->contact_phone);
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
