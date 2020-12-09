<?php declare(strict_types=1);

namespace Reconmap\Repositories;

use Reconmap\Models\Organisation;

class OrganisationRepository extends MysqlRepository
{
    private static int $rootOrganisationId = 1;

    public function findRootOrganisation(): Organisation
    {
        $stmt = $this->db->prepare('SELECT * FROM organisation WHERE id = 1');
        $stmt->bind_param('i', self::$rootOrganisationId);
        $stmt->execute();
        $rs = $stmt->get_result();
        $organisation = $rs->fetch_object(Organisation::class);
        $stmt->close();

        return $organisation;
    }
}
