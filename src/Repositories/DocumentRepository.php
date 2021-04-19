<?php declare(strict_types=1);

namespace Reconmap\Repositories;

use Reconmap\Repositories\QueryBuilders\SelectQueryBuilder;

class DocumentRepository extends MysqlRepository
{
    public const UPDATABLE_COLUMNS_TYPES = [
        'title' => 's',
        'content' => 's',
        'visibility' => 's',
    ];

    public function findByParentId(string $parentType, ?int $parentId): array
    {
        $queryBuilder = $this->getBaseSelectQueryBuilder();
        if (is_null($parentId)) {
            $queryBuilder->setWhere('n.parent_type = ? AND n.parent_id IS NULL');
        } else {
            $queryBuilder->setWhere('n.parent_type = ? AND n.parent_id = ?');
        }

        $stmt = $this->db->prepare($queryBuilder->toSql());
        if (is_null($parentId)) {
            $stmt->bind_param('s', $parentType);
        } else {
            $stmt->bind_param('si', $parentType, $parentId);
        }
        $stmt->execute();
        $rs = $stmt->get_result();
        $notes = $rs->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        return $notes;
    }

    public function findById(int $id): array
    {
        $stmt = $this->db->prepare('SELECT * FROM document WHERE id = ?');
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $rs = $stmt->get_result();
        $document = $rs->fetch_assoc();
        $stmt->close();

        return $document;
    }

    public function deleteById(int $id): bool
    {
        return $this->deleteByTableId('document', $id);
    }

    public function insert(int $userId, object $document): int
    {
        $stmt = $this->db->prepare('INSERT INTO document (user_id, parent_type, parent_id, visibility, title, content) VALUES (?, ?, ?, ?, ?, ?)');
        $stmt->bind_param('isisss', $userId, $document->parent_type, $document->parent_id, $document->visibility, $document->title, $document->content);
        return $this->executeInsertStatement($stmt);
    }

    private function getBaseSelectQueryBuilder(): SelectQueryBuilder
    {
        $queryBuilder = new SelectQueryBuilder('document AS n');
        $queryBuilder->addJoin('INNER JOIN user u ON (u.id = n.user_id)');
        $queryBuilder->setColumns('n.*, u.username AS user_name');
        $queryBuilder->setOrderBy('n.insert_ts DESC');

        return $queryBuilder;
    }

    public function findAll(): array
    {
        $resultSet = $this->db->query('SELECT * FROM document');
        return $resultSet->fetch_all(MYSQLI_ASSOC);
    }

    public function updateById(int $id, array $newColumnValues): bool
    {
        return $this->updateByTableId('document', $id, $newColumnValues);
    }
}
