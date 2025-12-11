<?php

declare(strict_types=1);

namespace Reconmap\Repositories;

use Ponup\SqlBuilders\InsertQueryBuilder;
use Reconmap\Models\Contact;

class ContactRepository extends MysqlRepository implements Deletable
{
    private const TABLE_NAME = 'contact';

    public function insert(Contact $contact): int
    {
        $insertStmt = new InsertQueryBuilder(self::TABLE_NAME);
        $insertStmt->setColumns('organisation_id, kind, name, email, phone, role');
        $stmt = $this->mysqlServer->prepare($insertStmt->toSql());
        $stmt->bind_param('isssss', $contact->organisation_id, $contact->kind, $contact->name, $contact->email, $contact->phone, $contact->role);
        return $this->executeInsertStatement($stmt);
    }

    public function findByClientId(int $clientId): array
    {
        $sql = <<<SQL
SELECT *
FROM contact c
WHERE
      organisation_id = ?
SQL;

        $stmt = $this->mysqlServer->prepare($sql);
        $stmt->bind_param('i', $clientId);

        $stmt->execute();
        $result = $stmt->get_result();
        $contacts = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        return $contacts;
    }

    public function deleteById(int $id): bool
    {
        return $this->deleteByTableId(self::TABLE_NAME, $id);
    }
}
