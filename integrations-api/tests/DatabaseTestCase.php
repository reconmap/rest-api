<?php declare(strict_types=1);

namespace Reconmap;

use PHPUnit\Framework\TestCase;
use Reconmap\Database\ConnectionFactory;
use Reconmap\Database\MysqlServer;

abstract class DatabaseTestCase extends TestCase
{
    use ApplicationConfigTestingTrait;

    public const array DATABASE_SETTINGS = [
        'host' => 'rmap-mysql',
        'username' => 'reconmapper',
        'password' => 'reconmapped',
        'name' => 'reconmap_test'
    ];

    public function getDatabaseConnection(): MysqlServer
    {
        $config = $this->createEmptyApplicationConfig();
        $config['database'] = self::DATABASE_SETTINGS;

        return ConnectionFactory::createConnection($config);
    }
}
