<?php declare(strict_types=1);

namespace Reconmap\Controllers\Commands;

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Reconmap\CommandOutputParsers\ProcessorFactory;

class GetCommandOutputParsersControllerTest extends TestCase
{
    public function testHappyPath()
    {
        $processorFactory = $this->createMock(ProcessorFactory::class);
        $processorFactory->expects($this->once())
            ->method('getAll')
            ->willReturn([]);
        $serverRequestInterface = $this->createMock(ServerRequestInterface::class);

        $controller = new GetCommandOutputParsersController($processorFactory);
        $response = $controller($serverRequestInterface);
        $this->assertIsArray(json_decode($response->getContent(), true));
    }
}
