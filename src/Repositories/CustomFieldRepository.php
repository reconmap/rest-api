<?php declare(strict_types=1);

namespace Reconmap\Repositories;

use Ponup\SqlBuilders\SelectQueryBuilder;
use Reconmap\Models\CustomField;

class CustomFieldRepository extends MysqlRepository implements Deletable
{
    public const array UPDATABLE_COLUMNS_TYPES = [
        'parent_type' => 's',
        'name' => 's',
        'label' => 's',
        'kind' => 's',
        'config' => 's',
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
        $result = $stmt->get_result();
        $documents = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        return $documents;
    }

    public function findById(int $id): ?array
    {
        $queryBuilder = $this->getBaseSelectQueryBuilder();
        $queryBuilder->setWhere('n.id = ?');
        $stmt = $this->db->prepare($queryBuilder->toSql());
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $document = $result->fetch_assoc();
        $stmt->close();

        return $document;
    }

    public function deleteById(int $id): bool
    {
        return $this->deleteByTableId('custom_field', $id);
    }

    public function insert(CustomField $customField): bool
    {
        $stmt = $this->db->prepare('INSERT INTO custom_field (parent_type, name, label, kind, config) VALUES (?, ?, ?, ?, ?)');
        return $stmt->execute([$customField->parent_type, $customField->name, $customField->label, $customField->kind, $customField->config]);
    }

    protected function getBaseSelectQueryBuilder(): SelectQueryBuilder
    {
        $queryBuilder = new SelectQueryBuilder('custom_field AS cf');
        $queryBuilder->setOrderBy('cf.insert_ts DESC');

        return $queryBuilder;
    }

    public function findAll(): array
    {
        $sql = $this->getBaseSelectQueryBuilder()->toSql();
        $resultSet = $this->db->query($sql);
        return $resultSet->fetch_all(MYSQLI_ASSOC);
    }

    public function updateById(int $id, array $newColumnValues): bool
    {
        return $this->updateByTableId('custom_field', $id, $newColumnValues);
    }
}
