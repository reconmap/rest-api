<?php declare(strict_types=1);

namespace Reconmap\Repositories;

use Reconmap\Models\Organisation;

class OrganisationRepository extends MysqlRepository
{
    private static int $rootOrganisationId = 1;

    public function findRootOrganisation(): ?Organisation
    {
        $stmt = $this->db->prepare('SELECT * FROM organisation WHERE id = ?');
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
        $stmt = $this->db->prepare('UPDATE organisation SET name = ?, url = ?, contact_name = ?, contact_email = ?, contact_phone = ?, logo = ?, small_logo = ?  WHERE id = ?');
        $stmt->bind_param('sssssssi', $organisation->name, $organisation->url, $organisation->contact_name, $organisation->contact_email, $organisation->contact_phone, $organisation->logo, $organisation->small_logo, self::$rootOrganisationId);
        $success = $stmt->execute();
        $stmt->close();

        return $success;
    }
}
