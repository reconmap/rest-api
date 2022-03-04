<?php declare(strict_types=1);

namespace Reconmap\Cli\Commands;

use Reconmap\Database\DatabaseSchemaMigrator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DatabaseMigratorCommand extends Command
{
    public function __construct(private readonly DatabaseSchemaMigrator $migrator)
    {
        parent::__construct();
    }

    protected function configure()
    {
        $this->setName('database:migrate-schema')
            ->setDescription('Migrates the database to newer schemas');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->migrator->run();

        return self::SUCCESS;
    }
}
