<?php declare(strict_types=1);

namespace Reconmap\Repositories\Importers;

use PHPUnit\Framework\TestCase;
use Reconmap\Repositories\CommandRepository;

class CommandsImporterTest extends TestCase
{
    public function testHappyPath()
    {
        $command = (object)[];

        $userId = 5;
        $commands = [$command];

        $mockCommandRepository = $this->createMock(CommandRepository::class);
        $mockCommandRepository->expects($this->once())
            ->method('insert')
            ->with((object)['creator_uid' => 5]);

        $importer = new CommandsImporter($mockCommandRepository);
        $result = $importer->import($userId, $commands);
        $this->assertEquals(1, $result['count']);
    }
}
