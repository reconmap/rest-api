<?php

declare(strict_types=1);

namespace Reconmap;

use PHPUnit\Framework\TestCase;

abstract class DatabaseTestCase extends TestCase
{
    public function getDatabaseConnection(): \mysqli
    {
        $db = new \mysqli('db', 'reconmapper', 'reconmapped', 'reconmap');
        return $db;
    }
}
