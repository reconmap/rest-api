<?php declare(strict_types=1);

namespace Reconmap\Cli\Commands;

use Reconmap\Services\QueueProcessor;
use Reconmap\Tasks\TaskResultProcessor;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class TaskProcessorCommand extends Command
{
    public function __construct(private readonly QueueProcessor      $queueProcessor,
                                private readonly TaskResultProcessor $taskResultProcessor)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setName('task:process')
            ->setDescription('Process pending task results');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        return $this->queueProcessor->run($this->taskResultProcessor, 'tasks:queue');
    }
}
