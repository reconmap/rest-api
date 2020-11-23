<?php

declare(strict_types=1);

namespace Reconmap;

use PHPUnit\Framework\TestCase;
use Reconmap\Services\Config;

abstract class DatabaseTestCase extends TestCase
{
    public function getDatabaseConnection(): \mysqli
    {
		$databaseSettings = [
			'host' => 'db',
			'username' => 'reconmapper',
			'password' => 'reconmapped',
			'name' => 'reconmap_test'
		];
        $config = new Config(['database' => $databaseSettings]);
        return DatabaseFactory::createConnection($config);
    }
}
