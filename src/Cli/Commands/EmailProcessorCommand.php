<?php declare(strict_types=1);

namespace Reconmap\Cli\Commands;

use Reconmap\QueueProcessor;
use Reconmap\Tasks\EmailTaskProcessor;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class EmailProcessorCommand extends Command
{
    public function __construct(private QueueProcessor     $queueProcessor,
                                private EmailTaskProcessor $emailTaskProcessor)
    {
        parent::__construct();
    }

    protected function configure()
    {
        $this->setName('email:process')
            ->setDescription('Process pending emails');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        return $this->queueProcessor->run($this->emailTaskProcessor, 'email:queue');
    }
}
