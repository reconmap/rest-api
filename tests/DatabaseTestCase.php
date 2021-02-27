<?php declare(strict_types=1);

namespace Reconmap;

use PHPUnit\Framework\TestCase;

abstract class DatabaseTestCase extends TestCase
{
    use ApplicationConfigTestingTrait;

    public const DATABASE_SETTINGS = [
        'host' => 'rmap-mysql',
        'username' => 'reconmapper',
        'password' => 'reconmapped',
        'name' => 'reconmap_test'
    ];

    public function getDatabaseConnection(): \mysqli
    {
        $config = $this->createEmptyApplicationConfig();
        $config['database'] = self::DATABASE_SETTINGS;

        return DatabaseFactory::createConnection($config);
    }
}
