<?php

declare(strict_types=1);

namespace Reconmap;

class DatabaseFactory
{

    static public function createConnection()
    {
        $driver = new \mysqli_driver();
        $driver->report_mode = MYSQLI_REPORT_ALL ^ MYSQLI_REPORT_INDEX;

        // @todo pull credentials from env variables
        $db = new \mysqli('db', 'reconmapper', 'reconmapped', 'reconmap');
        return $db;
    }
}
