<?php declare(strict_types=1);

namespace Reconmap\Repositories;

use Reconmap\DatabaseTestCase;
use Reconmap\Models\Command;

class CommandRepositoryTest extends DatabaseTestCase
{
    private CommandRepository $subject;

    public function setUp(): void
    {
        $this->subject = new CommandRepository($this->getDatabaseConnection());
    }

    public function testInsert()
    {
        $command = new Command();
        $command->creator_uid = 1;
        $command->name = 'Nmap';

        $this->assertTrue($this->subject->insert($command) >= 1);
    }

    public function testFindAll()
    {
        $commands = $this->subject->findAll();
        $this->assertCount(6, $commands);
    }


    public function testFindById()
    {
        $command = $this->subject->findById(1);
        $this->assertEquals('nmap', $command['name']);
    }

    public function testFindByIdNotFound()
    {
        $command = $this->subject->findById(-5);
        $this->assertNull($command);
    }
}
