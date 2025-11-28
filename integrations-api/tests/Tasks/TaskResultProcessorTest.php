<?php declare(strict_types=1);

namespace Reconmap\Tasks;

use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Reconmap\CommandOutputParsers\Models\Asset;
use Reconmap\CommandOutputParsers\Models\AssetKind;
use Reconmap\CommandOutputParsers\Models\ProcessorResult;
use Reconmap\CommandOutputParsers\NmapOutputProcessor;
use Reconmap\CommandOutputParsers\ProcessorFactory;
use Reconmap\Models\Target;
use Reconmap\Models\Vulnerability;
use Reconmap\Repositories\CommandUsageRepository;
use Reconmap\Repositories\NotificationsRepository;
use Reconmap\Repositories\TargetRepository;
use Reconmap\Repositories\VulnerabilityRepository;
use Reconmap\Services\RedisServer;

class TaskResultProcessorTest extends TestCase
{
    /**
     * @throws Exception
     */
    public function testSuccess()
    {
        $mockLogger = $this->createMock(LoggerInterface::class);
        $mockRedisServer = $this->createMock(RedisServer::class);
        $mockRedisServer->expects($this->once())
            ->method('lPush');
        $mockVulnerabilityRepository = $this->createMock(VulnerabilityRepository::class);
        $mockCommandUsageRepository = $this->createMock(CommandUsageRepository::class);
        $mockCommandUsageRepository->expects($this->once())
            ->method('findById')
            ->with(4)
            ->willReturn(['command_name' => 'Nmap', 'output_parser' => 'nmap', 'project_id' => 5]);

        $mockNotificationRepository = $this->createMock(NotificationsRepository::class);
        $mockNotificationRepository->expects($this->once())
            ->method('insert');

        $target = new Target();
        $target->project_id = 5;
        $target->name = 'new-host.local';
        $target->kind = AssetKind::Hostname->value;
        $target->tags = '["nmap"]';

        $mockTargetRepository = $this->createMock(TargetRepository::class);
        $mockTargetRepository->expects($this->once())
            ->method('findOrInsert')
            ->with($target);

        $mockProcessorFactory = $this->createMock(ProcessorFactory::class);

        $mockProcessorResult = new ProcessorResult();
        $mockVulnerability = new Vulnerability();
        $mockVulnerability->asset = new Asset(kind: AssetKind::Hostname, value: 'new-host.local');
        $mockProcessorResult->addVulnerability($mockVulnerability);

        $mockNmapResultProcessor = $this->createMock(NmapOutputProcessor::class);
        $mockNmapResultProcessor->expects($this->once())
            ->method('process')
            ->willReturn($mockProcessorResult);

        $mockProcessorFactory->expects($this->once())
            ->method('createFromOutputParserName')
            ->with('nmap')
            ->willReturn($mockNmapResultProcessor);

        $mockItem = new \stdClass();
        $mockItem->filePath = 'foo.bar';
        $mockItem->projectId = 5;
        $mockItem->commandUsageId = 4;
        $mockItem->userId = 1;

        $controller = new TaskResultProcessor($mockLogger, $mockRedisServer, $mockVulnerabilityRepository, $mockNotificationRepository, $mockCommandUsageRepository, $mockTargetRepository, $mockProcessorFactory);
        $controller->process($mockItem);
    }
}
