<?php declare(strict_types=1);

namespace Reconmap\Repositories;

use Reconmap\Repositories\QueryBuilders\SelectQueryBuilder;
use Reconmap\Repositories\QueryBuilders\UpdateQueryBuilder;

class UserRepository extends MysqlRepository
{
    public const UPDATABLE_COLUMNS_TYPES = [
        'full_name' => 's',
        'short_bio' => 's',
        'email' => 's',
        'role' => 's',
        'username' => 's',
        'password' => 's',
        'timezone' => 's'
    ];

    public function findAll(): array
    {
        $rs = $this->db->query('SELECT u.id, u.insert_ts, u.update_ts, u.full_name, u.username, u.email, u.role FROM user u LIMIT 20');
        return $rs->fetch_all(MYSQLI_ASSOC);
    }

    public function findById(int $id): ?array
    {
        $queryBuilder = $this->getBaseSelectQueryBuilder();
        $queryBuilder->setWhere('u.id = ?');

        $stmt = $this->db->prepare($queryBuilder->toSql());
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $rs = $stmt->get_result();
        $user = $rs->fetch_assoc();
        $stmt->close();

        return $user;
    }

    private function getBaseSelectQueryBuilder(): SelectQueryBuilder
    {
        $queryBuilder = new SelectQueryBuilder('user u');
        $queryBuilder->setColumns('u.id, u.insert_ts, u.update_ts, u.full_name, u.short_bio, u.username, u.email, u.password, u.role, u.timezone');
        return $queryBuilder;
    }

    public function findByUsername(string $username): ?array
    {
        $queryBuilder = $this->getBaseSelectQueryBuilder();
        $queryBuilder->setWhere('u.username = ?');

        $stmt = $this->db->prepare($queryBuilder->toSql());
        $stmt->bind_param('s', $username);
        $stmt->execute();
        $rs = $stmt->get_result();
        $user = $rs->fetch_assoc();
        $stmt->close();

        return $user;
    }

    public function deleteById(int $id): bool
    {
        $stmt = $this->db->prepare('DELETE FROM user WHERE id = ?');
        $stmt->bind_param('i', $id);
        $result = $stmt->execute();
        $success = $result && 1 === $stmt->affected_rows;
        $stmt->close();

        return $success;
    }

    public function deleteByIds(array $ids): int
    {
        $numSuccesses = 0;

        $stmt = $this->db->prepare('DELETE FROM user WHERE id = ?');
        $stmt->bind_param('i', $id);
        foreach ($ids as $id) {
            $result = $stmt->execute();
            $success = $result && 1 === $stmt->affected_rows;
            $numSuccesses += $success ? 1 : 0;
        }
        $stmt->close();

        return $numSuccesses;
    }

    public function create(object $user): int
    {
        $stmt = $this->db->prepare('INSERT INTO user (username, password, email, role) VALUES (?, ?, ?, ?)');
        $stmt->bind_param('ssss', $user->name, $user->password, $user->email, $user->role);
        return $this->executeInsertStatement($stmt);
    }

    public function findByProjectId(int $projectId): array
    {
        $sql = <<<SQL
        SELECT
            pu.id AS membership_id,
            u.id, u.insert_ts, u.update_ts, u.full_name, u.short_bio, u.username, u.email, u.role
        FROM
            user u INNER JOIN project_user pu ON (pu.user_id = u.id)
        WHERE
            project_id = ?
        ORDER BY
            u.username
        SQL;
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('i', $projectId);
        $stmt->execute();
        $rs = $stmt->get_result();
        $users = $rs->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $users;
    }

    public function updateById(int $id, array $newColumnValues): bool
    {
        $updateQueryBuilder = new UpdateQueryBuilder('user');
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
