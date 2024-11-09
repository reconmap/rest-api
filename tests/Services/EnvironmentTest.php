<?php

declare(strict_types=1);

namespace Reconmap\Services;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class EnvironmentTest extends TestCase
{
    public static function getTestValues(): array
    {
        return [
            ['CURRENT_ANIMAL', null],
            ['CURRENT_PLANET', 'Moon'],
        ];
    }

    #[DataProvider("getTestValues")]
    public function testValueIsReturned(string $propertyName, ?string $propertyValue)
    {
        $env = new Environment();
        $this->assertEquals($propertyValue, $env->getValue($propertyName));
    }
}
