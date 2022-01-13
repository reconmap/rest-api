<?php declare(strict_types=1);

namespace Reconmap\Repositories;

use Ponup\SqlBuilders\SelectQueryBuilder;
use Reconmap\Models\Notification;

class NotificationsRepository extends MysqlRepository implements Updateable, Deletable
{
    public const UPDATABLE_COLUMNS_TYPES = [
        'status' => 's',
    ];

    public function findAll(): array
    {
        $queryBuilder = $this->getBaseSelectQueryBuilder();
        $result = $this->db->query($queryBuilder->toSql());
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function deleteById(int $id): bool
    {
        return $this->deleteByTableId('notification', $id);
    }

    public function insert(Notification $notification): int
    {
        $stmt = $this->db->prepare('INSERT INTO notification (title, content) VALUES (?, ?)');
        $stmt->bind_param('ss', $notification->title, $notification->content);
        return $this->executeInsertStatement($stmt);
    }

    public function updateById(int $id, array $newColumnValues): bool
    {
        return $this->updateByTableId('notification', $id, $newColumnValues);
    }

    private function getBaseSelectQueryBuilder(): SelectQueryBuilder
    {
        $queryBuilder = new SelectQueryBuilder('notification n');
        $queryBuilder->setOrderBy('n.insert_ts DESC');
        return $queryBuilder;
    }
}
