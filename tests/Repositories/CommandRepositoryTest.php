<?php declare(strict_types=1);

namespace Reconmap\Repositories;

use Reconmap\DatabaseTestCase;

class CommandRepositoryTest extends DatabaseTestCase
{
    public function testInsert()
    {
        $command = new \stdClass();
        $command->creator_uid = 1;
        $command->short_name = 'nmap';

        $repository = new CommandRepository($this->getDatabaseConnection());
        $this->assertTrue($repository->insert($command) >= 1);
    }
}
