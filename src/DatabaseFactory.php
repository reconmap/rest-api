<?php

declare(strict_types=1);

namespace Reconmap;

use Reconmap\Services\Config;

class DatabaseFactory
{

    static public function createConnection(Config $config)
    {
        $driver = new \mysqli_driver();
        $driver->report_mode = MYSQLI_REPORT_ALL ^ MYSQLI_REPORT_INDEX;

        $dbSettings = $config->getSettings('database');
        $db = new \mysqli($dbSettings['host'], $dbSettings['username'], $dbSettings['password'], $dbSettings['name']);
        return $db;
    }
}
