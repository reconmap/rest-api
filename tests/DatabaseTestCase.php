<?php

declare(strict_types=1);

namespace Reconmap;

use PHPUnit\Framework\TestCase;
use Reconmap\Services\Config;

abstract class DatabaseTestCase extends TestCase
{
	public const DATABASE_SETTINGS = [
		'host' => 'rmap-mysql',
		'username' => 'reconmapper',
		'password' => 'reconmapped',
		'name' => 'reconmap_test'
	];

    public function getDatabaseConnection(): \mysqli
    {
        $config = new Config(['database' => self::DATABASE_SETTINGS]);
        return DatabaseFactory::createConnection($config);
    }
}
