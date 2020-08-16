<?php

declare(strict_types=1);

namespace Reconmap\Controllers;

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;

class ControllerTest extends TestCase
{

    public function testJwtTokenValidation()
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects($this->once())
            ->method('getHeader')
            ->willReturn(['Bearer eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJyZWNvbm1hcC5vcmciLCJhdWQiOiJyZWNvbm1hcC5jb20iLCJpYXQiOjE1MTYyMzkwMjJ9.vJPHA-5D0haWIeVT5wE73Z0OK7E4tBAbROb1AOtII0w']);

        /** @var IndexController */
        $controller = $this->getMockBuilder(IndexController::class)
            ->disableOriginalConstructor()
            ->onlyMethods([])
            ->getMock();
        $controller->validateJwtToken($request);
    }
}
