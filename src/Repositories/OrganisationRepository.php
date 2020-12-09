<?php declare(strict_types=1);

namespace Reconmap\Repositories;

use Reconmap\Models\Organisation;

class OrganisationRepository extends MysqlRepository
{
    private static int $rootOrganisationId = 1;

    public function findRootOrganisation(): Organisation
    {
        $stmt = $this->db->prepare('SELECT * FROM organisation WHERE id = ?');
        $stmt->bind_param('i', self::$rootOrganisationId);
        $stmt->execute();
        $rs = $stmt->get_result();
        $organisation = $rs->fetch_object(Organisation::class);
        $stmt->close();

        return $organisation;
    }

    /**
     * @param Organisation $organisation
     * @return bool
     */
    public function updateRootOrganisation(object $organisation): bool
    {
        $stmt = $this->db->prepare('UPDATE organisation SET name = ?, url = ?, contact_name = ?, contact_email = ?, contact_phone = ? WHERE id = ?');
        $stmt->bind_param('sssssi', $organisation->name, $organisation->url, $organisation->contactName, $organisation->contactEmail, $organisation->contactPhone, self::$rootOrganisationId);
        $result = $stmt->execute();
        $stmt->close();

        return $result;
    }
}
