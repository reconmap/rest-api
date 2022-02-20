<?php declare(strict_types=1);

namespace Reconmap\Repositories;

use Reconmap\DatabaseTestCase;
use Reconmap\Models\Organisation;

class OrganisationRepositoryTest extends DatabaseTestCase
{
    private OrganisationRepository $subject;

    public function setUp(): void
    {
        $this->subject = new OrganisationRepository($this->getDatabaseConnection());
    }

    public function testFindRootOrganisation()
    {
        $rootOrg = $this->subject->findRootOrganisation();
        $this->assertEquals('https://reconmap.org', $rootOrg->url);
    }

    public function testUpdateRootOrganisation()
    {
        $memoryOrg = new Organisation();
        $memoryOrg->name = 'Updated org';
        $memoryOrg->contact_name = 'Elliot Alderson';
        $memoryOrg->contact_email = 'ealderson@fsociety.org';

        $this->subject->updateRootOrganisation($memoryOrg);

        $dbOrg = $this->subject->findRootOrganisation();

        $this->assertEquals('Updated org', $dbOrg->name);
    }
}
