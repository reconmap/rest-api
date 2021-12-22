<?php declare(strict_types=1);

namespace Reconmap\Cli\Commands;

use Reconmap\QueueProcessor;
use Reconmap\Tasks\TaskResultProcessor;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class TaskProcessorCommand extends Command
{
    public function __construct(private QueueProcessor      $queueProcessor,
                                private TaskResultProcessor $taskResultProcessor)
    {
        parent::__construct();
    }

    protected function configure()
    {
        $this->setName('task:process')
            ->setDescription('Process pending task results')
            ->addOption('use-default-database', null, InputOption::VALUE_NONE, 'Whether to use the default or test database');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        return $this->queueProcessor->run($this->taskResultProcessor, 'tasks:queue');
    }
}
