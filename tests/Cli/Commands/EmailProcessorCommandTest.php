<?php declare(strict_types=1);

namespace Reconmap\Cli\Commands;

use PHPUnit\Framework\TestCase;
use Reconmap\QueueProcessor;
use Reconmap\Tasks\EmailTaskProcessor;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class EmailProcessorCommandTest extends TestCase
{
    public function testHappyPath()
    {
        $mockEmailTaskProcessor = $this->createMock(EmailTaskProcessor::class);
        $mockQueueProcessor = $this->createMock(QueueProcessor::class);
        $mockQueueProcessor->expects($this->once())
            ->method('run')
            ->with($mockEmailTaskProcessor, 'email:queue');

        $mockInputInterface = $this->createMock(InputInterface::class);
        $mockOutputInterface = $this->createMock(OutputInterface::class);

        $command = new EmailProcessorCommand($mockQueueProcessor, $mockEmailTaskProcessor);
        $command->run($mockInputInterface, $mockOutputInterface);
    }
}
