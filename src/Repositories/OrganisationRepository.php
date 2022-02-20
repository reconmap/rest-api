<?php declare(strict_types=1);

namespace Reconmap\Repositories;

use Reconmap\Models\Organisation;

class OrganisationRepository extends MysqlRepository
{
    private static int $rootOrganisationId = 1;

    public function findRootOrganisation(): ?Organisation
    {
        $sql = <<<SQL
SELECT
    o.*, c.name AS contact_name, c.email AS contact_email, c.phone AS contact_phone
FROM
    organisation o
INNER JOIN
    contact c ON c.id = o.contact_id
WHERE o.id = ?
SQL;

        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('i', self::$rootOrganisationId);
        $stmt->execute();
        $result = $stmt->get_result();
        $organisation = $result->fetch_object(Organisation::class);
        $result->close();
        $stmt->close();

        return $organisation;
    }

    public function updateRootOrganisation(Organisation $organisation): bool
    {
        $sql = <<<SQL
UPDATE organisation o
    INNER JOIN contact c ON c.id = o.contact_id
SET
    o.name = ?, o.url = ?,
    c.name = ?, c.email = ?, c.phone = ? WHERE o.id = ?
SQL;

        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('sssssi', $organisation->name, $organisation->url, $organisation->contact_name, $organisation->contact_email, $organisation->contact_phone, self::$rootOrganisationId);
        $success = $stmt->execute();
        $stmt->close();

        return $success;
    }
}
