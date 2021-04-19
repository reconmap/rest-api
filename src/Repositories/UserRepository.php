<?php declare(strict_types=1);

namespace Reconmap\Repositories;

use Reconmap\Models\User;
use Reconmap\Repositories\QueryBuilders\InsertQueryBuilder;
use Reconmap\Repositories\QueryBuilders\SelectQueryBuilder;

class UserRepository extends MysqlRepository
{
    public const UPDATABLE_COLUMNS_TYPES = [
        'active' => 'i',
        'full_name' => 's',
        'short_bio' => 's',
        'email' => 's',
        'role' => 's',
        'username' => 's',
        'password' => 's',
        'mfa_enabled' => 'i',
        'mfa_secret' => 's',
        'timezone' => 's'
    ];

    public function findAll(): array
    {
        $queryBuilder = $this->getBaseSelectQueryBuilder();
        $queryBuilder->setLimit(20);

        $result = $this->db->query($queryBuilder->toSql());
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function findById(int $id, bool $includePassword = false): ?array
    {
        $queryBuilder = $this->getBaseSelectQueryBuilder();
        $queryBuilder->setColumns($queryBuilder->getColumns() . ', u.mfa_secret' . ($includePassword ? ', u.password' : ''));
        $queryBuilder->setWhere('u.id = ?');

        $stmt = $this->db->prepare($queryBuilder->toSql());
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        $stmt->close();

        return $user;
    }

    private function getBaseSelectQueryBuilder(): SelectQueryBuilder
    {
        $queryBuilder = new SelectQueryBuilder('user u');
        $queryBuilder->setColumns('u.id, u.insert_ts, u.update_ts, u.active, u.full_name, u.short_bio, u.username, u.email, u.role, u.timezone, u.mfa_enabled');
        return $queryBuilder;
    }

    public function findByUsername(string $username): ?array
    {
        $queryBuilder = $this->getBaseSelectQueryBuilder();
        $queryBuilder->setColumns($queryBuilder->getColumns() . ', u.password, u.mfa_secret');
        $queryBuilder->setWhere('u.active AND u.username = ?');

        $stmt = $this->db->prepare($queryBuilder->toSql());
        $stmt->bind_param('s', $username);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        $stmt->close();

        return $user;
    }

    public function deleteById(int $id): bool
    {
        return $this->deleteByTableId('user', $id);
    }

    public function deleteByIds(array $ids): int
    {
        return $this->deleteByTableIds('user', $ids);
    }

    public function create(User $user): int
    {
        $insertStmt = new InsertQueryBuilder('user');
        $insertStmt->setColumns('active, full_name, short_bio, username, password, mfa_enabled, mfa_secret, email, role');
        $stmt = $this->db->prepare($insertStmt->toSql());
        $stmt->bind_param('issssisss', $user->active, $user->full_name, $user->short_bio, $user->username, $user->password, $user->mfa_enabled, $user->mfa_secret, $user->email, $user->role);
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
        return $this->updateByTableId('user', $id, $newColumnValues);
    }
}
