<?php declare(strict_types=1);

namespace Reconmap\Controllers\System;

use Psr\Http\Message\ServerRequestInterface;
use Reconmap\ControllerTestCase;
use Reconmap\Services\ApplicationConfig;

class GetApplicationLogsControllerTest extends ControllerTestCase
{
    private const LOG_DIR = __DIR__ . DIRECTORY_SEPARATOR . 'logs';
    private const LOG_FILE = self::LOG_DIR . DIRECTORY_SEPARATOR . 'application.log';

    public function setUp(): void
    {
        if (!is_dir(self::LOG_DIR))
            mkdir(self::LOG_DIR);
        file_put_contents(self::LOG_FILE, '2021-07-13 DEBUG A good thing happened');
    }

    public function tearDown(): void
    {
        unlink(self::LOG_FILE);
        rmdir(self::LOG_DIR);
    }

    public function testHappyPath()
    {
        $mockAppConfig = $this->createMock(ApplicationConfig::class);
        $mockAppConfig->expects($this->once())
            ->method('getAppDir')
            ->willReturn(__DIR__);

        $mockRequest = $this->createMock(ServerRequestInterface::class);

        $mockAuthorisationService = $this->createAuthorisationServiceMock();

        $controller = new GetApplicationLogsController($mockAuthorisationService, $mockAppConfig);
        $response = $controller($mockRequest);
        $this->assertEquals('text/plain', $response->getHeaderLine('Content-type'));
        $this->assertEquals('2021-07-13 DEBUG A good thing happened', $response->getBody()->getContents());
    }
}
