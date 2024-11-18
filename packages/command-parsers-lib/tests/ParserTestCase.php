<?php declare(strict_types=1);

namespace Reconmap\CommandOutputParsers;

use PHPUnit\Framework\TestCase;

abstract class ParserTestCase extends TestCase
{

    public function getResourceFilePath(string $fileName) : string
    {
        return dirname(__DIR__, 1) . '/resources/' . $fileName;
    }
}
