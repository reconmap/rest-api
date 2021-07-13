<?php declare(strict_types=1);

namespace Reconmap\Controllers\System;

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Repositories\IntegrationsRepository;

class GetIntegrationsControllerTest extends TestCase
{
    public function testBasic()
    {
        $mockRepository = $this->createMock(IntegrationsRepository::class);
        $mockRepository->expects($this->once())
            ->method('findAll')
            ->willReturn([]);

        $mockRequest = $this->createMock(ServerRequestInterface::class);

        $controller = new GetIntegrationsController($mockRepository);
        $response = $controller($mockRequest);
        $this->assertEquals([], $response);
    }
}
