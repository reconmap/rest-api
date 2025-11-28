<?php declare(strict_types=1);

namespace Reconmap\Repositories\Exporters;

use PHPUnit\Framework\TestCase;
use Reconmap\Repositories\UserRepository;

class UsersExporterTest extends TestCase
{
    public function testHappyPath()
    {
        $mockRepository = $this->createMock(UserRepository::class);
        $mockRepository->expects($this->once())
            ->method('findAll')
            ->willReturn([]);
        $exporter = new UsersExporter($mockRepository);
        $this->assertEquals([], $exporter->export());
    }
}
