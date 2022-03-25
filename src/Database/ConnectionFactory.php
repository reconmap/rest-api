<?php declare(strict_types=1);

namespace Reconmap\Database;

use Reconmap\Services\ApplicationConfig;

class ConnectionFactory
{

    static public function createConnection(ApplicationConfig $config): \mysqli
    {
        $driver = new \mysqli_driver();
        $driver->report_mode = MYSQLI_REPORT_ALL ^ MYSQLI_REPORT_INDEX;

        $dbSettings = $config->getSettings('database');
        $conn = new \mysqli;
        $conn->options(MYSQLI_OPT_INT_AND_FLOAT_NATIVE, true);
        $conn->real_connect($dbSettings['host'], $dbSettings['username'], $dbSettings['password'], $dbSettings['name'], flags: MYSQLI_CLIENT_FOUND_ROWS);

        return $conn;
    }
}
