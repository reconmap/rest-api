<?php declare(strict_types=1);

namespace Reconmap\Cli\Commands;

use Reconmap\Database\TestDataGenerator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class TestDataGeneratorCommand extends Command
{
    public function __construct(private readonly TestDataGenerator $testDataGenerator)
    {
        parent::__construct();
    }

    protected function configure()
    {
        $this->setName('test:generate-data')
            ->setDescription('Generate test data');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->testDataGenerator->generate();

        return self::SUCCESS;
    }
}
