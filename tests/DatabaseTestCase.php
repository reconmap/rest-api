<?php

declare(strict_types=1);

namespace Reconmap;

use PHPUnit\Framework\TestCase;
use Reconmap\Services\Config;

abstract class DatabaseTestCase extends TestCase
{
    public function getDatabaseConnection(): \mysqli
    {
        $config = new Config(['database' => ['host' => 'db', 'username' => 'reconmapper', 'password' => 'reconmapped', 'name' => 'reconmap']]);
        return DatabaseFactory::createConnection($config);
    }
}
