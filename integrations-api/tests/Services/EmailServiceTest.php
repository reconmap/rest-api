<?php declare(strict_types=1);

namespace Reconmap\Services;

use Monolog\Logger;
use PHPUnit\Framework\TestCase;

class EmailServiceTest extends TestCase
{
    public function testDefault()
    {
        $mockTemplateEngine = $this->createMock(TemplateEngine::class);
        $mockTemplateEngine->expects($this->once())
            ->method('render')
            ->with('dir/template', ['foo' => 'bar?', 'instance_url' => 'reconmap.local'])
            ->willReturn('Is foo bar?');

        $mockRedisServer = $this->createMock(RedisServer::class);
        $mockRedisServer->expects($this->once())
            ->method('lPush')
            ->with('email:queue', '{"subject":"Just a test","to":["me"],"body":"Is foo bar?"}');

        $mockApplicationConfig = $this->createMock(ApplicationConfig::class);
        $mockApplicationConfig->expects($this->once())
            ->method('getSettings')
            ->with('cors')
            ->willReturn(['allowedOrigins' => ['reconmap.local']]);

        $mockLogger = $this->createMock(Logger::class);

        $subject = new EmailService($mockTemplateEngine, $mockRedisServer, $mockApplicationConfig, $mockLogger);
        $subject->queueTemplatedEmail('dir/template', ['foo' => 'bar?'], 'Just a test', ['me']);
    }
}
