<?php declare(strict_types=1);

namespace Reconmap\Cli\Commands;

use Reconmap\Services\Reporting\WeeklyReportGenerator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class WeeklyEmailReportSenderCommand extends Command
{
    public function __construct(private readonly WeeklyReportGenerator $reportGenerator)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setName('email:send-weekly-report')
            ->setDescription('Send weekly report emails');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->reportGenerator->generate();

        return self::SUCCESS;
    }
}
