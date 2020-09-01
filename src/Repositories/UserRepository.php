<?php

declare(strict_types=1);

namespace Reconmap\Repositories;

class UserRepository
{

    private $db;

    public function __construct(\mysqli $db)
    {
        $this->db = $db;
    }

    public function findAll(): array
    {
        $rs = $this->db->query('SELECT u.id, u.insert_ts, u.update_ts, u.name, u.email, u.role FROM user u LIMIT 20');
        $projects = $rs->fetch_all(MYSQLI_ASSOC);
        return $projects;
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT u.id, u.insert_ts, u.update_ts, u.name, u.email, u.role FROM user u WHERE u.id = ?');
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $rs = $stmt->get_result();
        $project = $rs->fetch_assoc();
        $stmt->close();

        return $project;
    }

    public function findByUsernamePassword(string $username, string $password): array
    {
        $stmt = $this->db->prepare('SELECT u.id, u.insert_ts, u.update_ts, u.name, u.email, u.role FROM user u WHERE u.name = ? AND u.password = ?');
        $stmt->bind_param('ss', $username, $password);
        $stmt->execute();
        $rs = $stmt->get_result();
        $user = $rs->fetch_assoc();
        $stmt->close();

        return $user;
    }

    public function findByUsername(string $username): ?array
    {
        $stmt = $this->db->prepare('SELECT u.id, u.insert_ts, u.update_ts, u.name, u.email, u.password, u.role, u.timezone FROM user u WHERE u.name = ?');
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

    public function create(object $user): bool
    {
        $stmt = $this->db->prepare('INSERT INTO user (name, password, email, role) VALUES (?, ?, ?, ?)');
        $stmt->bind_param('ssss', $user->name, $user->password, $user->email, $user->role);
        $result = $stmt->execute();
        $success = $result && 1 === $stmt->affected_rows;
        $stmt->close();

        return $success;
    }

    public function findByProjectId(int $projectId): array {
        $sql = <<<SQL
        SELECT
            pu.id AS membership_id,
            u.id, u.insert_ts, u.update_ts, u.name, u.email, u.role
        FROM
            user u INNER JOIN project_user pu ON (pu.user_id = u.id)
        WHERE
            project_id = ?
        ORDER BY
            u.name ASC
        SQL;
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('i', $projectId);
        $stmt->execute();
        $rs = $stmt->get_result();
        $users = $rs->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $users;
    }

    public function updateById(int $id, string $column, string $value): bool
    {
        $stmt = $this->db->prepare('UPDATE user SET ' . $column . ' = ? WHERE id = ?');
        $stmt->bind_param('si', $value, $id);
        $result = $stmt->execute();
        $success = $result && 1 === $stmt->affected_rows;
        $stmt->close();

        return $success;
    }
}
