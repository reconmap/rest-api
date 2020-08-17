<?php

declare(strict_types=1);

namespace Reconmap\Repositories;

class ProjectRepository
{

    private $db;

    public function __construct(\mysqli $db)
    {
        $this->db = $db;
    }

    public function findAll(): array
    {
        $rs = $this->db->query('SELECT * FROM project');
        $projects = $rs->fetch_all(MYSQLI_ASSOC);
        return $projects;
    }

    public function findTemplateProjects(int $isTemplate): array
    {
        $stmt = $this->db->prepare('SELECT * FROM project WHERE is_template = ?');
        $stmt->bind_param('i', $isTemplate);
        $stmt->execute();
        $rs = $stmt->get_result();
        $projects = $rs->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $projects;
    }
}
