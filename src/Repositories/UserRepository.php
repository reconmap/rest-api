<?php declare(strict_types=1);

namespace Reconmap\Repositories;

use Ponup\SqlBuilders\InsertQueryBuilder;
use Ponup\SqlBuilders\SelectQueryBuilder;
use Reconmap\Models\User;

class UserRepository extends MysqlRepository
{
    public const UPDATABLE_COLUMNS_TYPES = [
        'active' => 'i',
        'full_name' => 's',
        'short_bio' => 's',
        'email' => 's',
        'role' => 's',
        'username' => 's',
        'timezone' => 's',
        'preferences' => 's',
    ];

    public function findAll(): array
    {
        $queryBuilder = $this->getBaseSelectQueryBuilder();

        $result = $this->db->query($queryBuilder->toSql());
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function findById(int $id): ?array
    {
        $queryBuilder = $this->getBaseSelectQueryBuilder();
        $queryBuilder->setWhere('u.id = ?');

        $stmt = $this->db->prepare($queryBuilder->toSql());
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        $stmt->close();

        return $user;
    }

    public function findBySubjectId(string $subjectId): ?array
    {
        $queryBuilder = $this->getBaseSelectQueryBuilder();
        $queryBuilder->setColumns($queryBuilder->getColumns());
        $queryBuilder->setWhere('u.subject_id = ?');

        $stmt = $this->db->prepare($queryBuilder->toSql());
        $stmt->bind_param('s', $subjectId);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        $stmt->close();

        return $user;
    }

    protected function getBaseSelectQueryBuilder(): SelectQueryBuilder
    {
        $queryBuilder = new SelectQueryBuilder('user u');
        $queryBuilder->setColumns('u.id, u.insert_ts, u.update_ts, u.subject_id, u.active, u.full_name, u.short_bio, u.username, u.email, u.role, u.timezone, u.preferences');
        return $queryBuilder;
    }

    public function findByUsername(string $username): ?array
    {
        $queryBuilder = $this->getBaseSelectQueryBuilder();
        $queryBuilder->setColumns($queryBuilder->getColumns());
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
        $insertStmt->setColumns('subject_id, active, full_name, short_bio, username, email, role');
        $stmt = $this->db->prepare($insertStmt->toSql());
        $stmt->bind_param('sisssss', $user->subject_id, $user->active, $user->full_name, $user->short_bio, $user->username, $user->email, $user->role);
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
        $result = $stmt->get_result();
        $users = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $users;
    }

    public function updateById(int $id, array $newColumnValues): bool
    {
        return $this->updateByTableId('user', $id, $newColumnValues);
    }
}
