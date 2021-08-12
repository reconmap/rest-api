<?php declare(strict_types=1);

namespace Reconmap\Tasks;

use Monolog\Logger;
use PHPUnit\Framework\TestCase;
use Reconmap\Models\Vulnerability;
use Reconmap\Processors\NmapOutputProcessor;
use Reconmap\Processors\ProcessorFactory;
use Reconmap\Repositories\TargetRepository;
use Reconmap\Repositories\TaskRepository;
use Reconmap\Repositories\VulnerabilityRepository;
use Reconmap\Services\ApplicationConfig;
use Reconmap\Services\RedisServer;

class TaskResultProcessorTest extends TestCase
{
    public function testSuccess()
    {
        $mockAppConfig = $this->createMock(ApplicationConfig::class);
        $mockLogger = $this->createMock(Logger::class);
        $mockMysql = $this->createMock(\mysqli::class);
        $mockRedisServer = $this->createMock(RedisServer::class);
        $mockRedisServer->expects($this->once())
            ->method('lPush');
        $mockVulnerabilityRepository = $this->createMock(VulnerabilityRepository::class);
        $mockTaskRepository = $this->createMock(TaskRepository::class);
        $mockTaskRepository->expects($this->once())
            ->method('findById')
            ->with(4)
            ->willReturn(['command_short_name' => 'nmap', 'project_id' => 5]);
        $mockTargetRepository = $this->createMock(TargetRepository::class);
        $mockTargetRepository->expects($this->once())
            ->method('insert');

        $mockProcessorFactory = $this->createMock(ProcessorFactory::class);

        $mockVulnerability = new Vulnerability();
        $mockVulnerability->host = (object)['name' => 'new-host.local'];

        $mockNmapResultProcessor = $this->createMock(NmapOutputProcessor::class);
        $mockNmapResultProcessor->expects($this->once())
            ->method('parseVulnerabilities')
            ->willReturn([$mockVulnerability]);

        $mockProcessorFactory->expects($this->once())
            ->method('createByCommandShortName')
            ->with('nmap')
            ->willReturn($mockNmapResultProcessor);

        $mockItem = new \stdClass();
        $mockItem->filePath = 'foo.bar';
        $mockItem->taskId = 4;
        $mockItem->userId = 1;

        $controller = new TaskResultProcessor($mockAppConfig, $mockLogger, $mockMysql, $mockRedisServer, $mockVulnerabilityRepository, $mockTaskRepository, $mockTargetRepository, $mockProcessorFactory);
        $controller->process($mockItem);
    }
}
