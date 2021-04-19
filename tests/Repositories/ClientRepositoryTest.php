<?php declare(strict_types=1);

namespace Reconmap\Repositories;

use Reconmap\DatabaseTestCase;
use Reconmap\Models\Client;

class ClientRepositoryTest extends DatabaseTestCase
{
    private ClientRepository $subject;

    public function setUp(): void
    {
        $this->subject = new ClientRepository($this->getDatabaseConnection());
    }

    public function testFindAll()
    {
        $clients = $this->subject->findAll();
        $this->assertCount(2, $clients);
    }

    public function testFindExistingClient()
    {
        $client = $this->subject->findById(1);
        $this->assertEquals(1, $client->getId());
        $this->assertEquals('Insecure Co.', $client->getName());
    }

    public function testFindUnexistentClient()
    {
        $client = $this->subject->findById(590);
        $this->assertNull($client);
    }

    public function testDeleteInvalidClient()
    {
        $this->assertFalse($this->subject->deleteById(-100));
    }

    public function testSuccessfulInsert()
    {
        $client = new Client();
        $client->creator_uid = 1;
        $client->name = 'Awesome client';
        $client->contact_name = 'Some Body';
        $client->contact_email = 'some@body';
        $this->assertIsInt($this->subject->insert($client));
    }

    public function testUnsuccessfulInsert()
    {
        $this->expectException(\mysqli_sql_exception::class);

        $client = new Client();
        $client->creator_uid = 1;
        $this->subject->insert($client);
    }

    public function testUpdate()
    {
        $client = $this->subject->findById(1);
        $this->assertEquals('Insecure Co.', $client->name);

        $this->assertTrue($this->subject->updateById(1, ['name' => 'A better name']));

        $client = $this->subject->findById(1);
        $this->assertEquals('A better name', $client->name);
    }
}
