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
        $command->output_parser = 'nmap';
        $command->executable_type = 'custom';
        $command->executable_path = 'nmap';

        $this->assertTrue($this->subject->insert($command) >= 1);
    }

    public function testFindAll()
    {
        $commands = $this->subject->findAll();
        $this->assertCount(5, $commands);
    }


    public function testFindById()
    {
        $command = $this->subject->findById(1);
        $this->assertEquals('Goohost', $command['name']);
    }

    public function testFindByIdNotFound()
    {
        $command = $this->subject->findById(-5);
        $this->assertNull($command);
    }
}
