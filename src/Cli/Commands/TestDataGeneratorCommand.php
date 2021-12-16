<?php declare(strict_types=1);

namespace Reconmap\Cli\Commands;

use Reconmap\Database\TestDataGenerator;
use Reconmap\DatabaseFactory;
use Reconmap\Services\ApplicationConfig;
use Reconmap\Services\ApplicationContainer;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class TestDataGeneratorCommand extends Command
{
    public function __construct(private TestDataGenerator    $testDataGenerator,
                                private ApplicationConfig    $applicationConfig,
                                private ApplicationContainer $applicationContainer)
    {
        parent::__construct();
    }

    protected function configure()
    {
        $this->setName('test:generate-data')
            ->setDescription('Generate test data')
            ->addOption('use-default-database', null, InputOption::VALUE_NONE, 'Whether to use the default or test database');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if (!$input->getOption('use-default-database')) {
            $this->applicationConfig['database'] = [
                'host' => 'rmap-mysql',
                'username' => 'reconmapper',
                'password' => 'reconmapped',
                'name' => 'reconmap_test'
            ];
            $this->applicationContainer->add(\mysqli::class, DatabaseFactory::createConnection($this->applicationConfig));

        }
        $this->testDataGenerator->generate();

        return self::SUCCESS;
    }
}
