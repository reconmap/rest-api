<?php declare(strict_types=1);

namespace Reconmap\Repositories;

use Reconmap\DatabaseTestCase;

class CommandRepositoryTest extends DatabaseTestCase
{
    private CommandRepository $subject;

    public function setUp(): void
    {
        $this->subject = new CommandRepository($this->getDatabaseConnection());
    }

    public function testInsert()
    {
        $command = new \stdClass();
        $command->creator_uid = 1;
        $command->short_name = 'nmap';
        $command->executable_type = 'custom';
        $command->executable_path = 'nmap';

        $this->assertTrue($this->subject->insert($command) >= 1);
    }

    public function testFindById()
    {
        $command = $this->subject->findById(1);
        $this->assertEquals('goohost', $command['short_name']);
    }

    public function testFindByIdNotFound()
    {
        $command = $this->subject->findById(-5);
        $this->assertNull($command);
    }
}
