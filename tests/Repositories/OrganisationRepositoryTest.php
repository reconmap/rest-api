<?php declare(strict_types=1);

namespace Reconmap\Repositories;

use Reconmap\DatabaseTestCase;

class OrganisationRepositoryTest extends DatabaseTestCase
{

    public function testFindRootOrganisation()
    {
        $repository = new OrganisationRepository($this->getDatabaseConnection());
        $rootOrg = $repository->findRootOrganisation();
        $this->assertEquals('https://reconmap.org', $rootOrg->url);
    }
}
