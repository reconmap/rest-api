<?php declare(strict_types=1);

namespace Reconmap\Repositories;

use Ponup\SqlBuilders\SelectQueryBuilder;
use Reconmap\Models\Document;

class DocumentRepository extends MysqlRepository implements Deletable
{
    public const array UPDATABLE_COLUMNS_TYPES = [
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

        $stmt = $this->mysqlServer->prepare($queryBuilder->toSql());
        if (is_null($parentId)) {
            $stmt->bind_param('s', $parentType);
        } else {
            $stmt->bind_param('si', $parentType, $parentId);
        }
        $stmt->execute();
        $result = $stmt->get_result();
        $documents = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        return $documents;
    }

    public function findById(int $id): ?array
    {
        $queryBuilder = $this->getBaseSelectQueryBuilder();
        $queryBuilder->setWhere('n.id = ?');
        $stmt = $this->mysqlServer->prepare($queryBuilder->toSql());
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $document = $result->fetch_assoc();
        $stmt->close();

        return $document;
    }

    public function deleteById(int $id): bool
    {
        return $this->deleteByTableId('document', $id);
    }

    public function insert(Document|\Reconmap\DomainObjects\Document $document): int
    {
        $stmt = $this->mysqlServer->prepare('INSERT INTO document (user_id, parent_type, parent_id, visibility, title, content) VALUES (?, ?, ?, ?, ?, ?)');
        $stmt->bind_param('isisss', $document->user_id, $document->parent_type, $document->parent_id, $document->visibility, $document->title, $document->content);
        return $this->executeInsertStatement($stmt);
    }

    protected function getBaseSelectQueryBuilder(): SelectQueryBuilder
    {
        $queryBuilder = new SelectQueryBuilder('document AS n');
        $queryBuilder->addJoin('INNER JOIN user u ON (u.id = n.user_id)');
        $queryBuilder->setColumns('n.*, u.username AS user_name');
        $queryBuilder->setOrderBy('n.insert_ts DESC');

        return $queryBuilder;
    }

    public function findAll(): array
    {
        $sql = $this->getBaseSelectQueryBuilder()->toSql();
        $resultSet = $this->mysqlServer->query($sql);
        return $resultSet->fetch_all(MYSQLI_ASSOC);
    }

    public function updateById(int $id, array $newColumnValues): bool
    {
        return $this->updateByTableId('document', $id, $newColumnValues);
    }
}
