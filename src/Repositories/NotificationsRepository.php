<?php declare(strict_types=1);

namespace Reconmap\Repositories;

use Ponup\SqlBuilders\SearchCriteria;
use Ponup\SqlBuilders\SelectQueryBuilder;
use Reconmap\Models\Notification;
use Reconmap\Services\PaginationRequestHandler;

class NotificationsRepository extends MysqlRepository implements Updateable, Deletable
{
    public const array UPDATABLE_COLUMNS_TYPES = [
        'status' => 's',
    ];

    public function insert(Notification $notification): int
    {
        $stmt = $this->mysqlServer->prepare('INSERT INTO notification (to_user_id, title, content) VALUES (?, ?, ?)');
        $stmt->bind_param('iss', $notification->toUserId, $notification->title, $notification->content);
        return $this->executeInsertStatement($stmt);
    }

    public function search(SearchCriteria $searchCriteria, ?PaginationRequestHandler $paginator = null): array
    {
        $queryBuilder = $this->getBaseSelectQueryBuilder();
        return $this->searchAll($queryBuilder, $searchCriteria, $paginator);
    }

    public function bulkUpdateStatusByUserId(array $notificationIds, string $status, int $userId): bool
    {
        $notificationIdsString = implode(',', array_filter($notificationIds, 'is_numeric'));
        $stmt = $this->mysqlServer->prepare('UPDATE notification SET status = ? WHERE to_user_id = ? AND id IN (' . $notificationIdsString . ')');
        return $stmt->execute([$status, $userId]);
    }

    public function updateById(int $id, array $newColumnValues): bool
    {
        return $this->updateByTableId('notification', $id, $newColumnValues);
    }

    public function deleteById(int $id): bool
    {
        return $this->deleteByTableId('notification', $id);
    }

    protected function getBaseSelectQueryBuilder(): SelectQueryBuilder
    {
        $queryBuilder = new SelectQueryBuilder('notification n');
        $queryBuilder->setOrderBy('n.insert_ts DESC');
        return $queryBuilder;
    }
}
