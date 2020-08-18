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
        $rs = $this->db->query('SELECT * FROM user');
        $projects = $rs->fetch_all(MYSQLI_ASSOC);
        return $projects;
    }

    public function findById(int $id): array
    {
        $stmt = $this->db->prepare('SELECT * FROM user WHERE id = ?');
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $rs = $stmt->get_result();
        $project = $rs->fetch_assoc();
        $stmt->close();

        return $project;
    }

    public function findByUsernamePassword(string $username, string $password): array
    {
        $stmt = $this->db->prepare('SELECT * FROM user WHERE name = ? AND password = ?');
        $stmt->bind_param('ss', $username, $password);
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
}
