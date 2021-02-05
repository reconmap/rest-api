<?php declare(strict_types=1);

namespace Reconmap\Repositories;

use Reconmap\DatabaseTestCase;

class ClientRepositoryTest extends DatabaseTestCase
{
    private ClientRepository $subject;

    public function setUp(): void
    {
        $this->subject = new ClientRepository($this->getDatabaseConnection());
    }

    public function testClientClassIsPopulated()
    {
        $client = $this->subject->findById(1);
        $this->assertEquals(1, $client->getId());
        $this->assertEquals('Insecure Co.', $client->getName());
    }

    public function testFindByInvalidIdReturnsNull()
    {
        $client = $this->subject->findById(590);
        $this->assertNull($client);
    }
}
