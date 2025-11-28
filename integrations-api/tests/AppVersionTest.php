<?php declare(strict_types=1);

namespace Reconmap;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class AppVersionTest extends TestCase
{
    public static function dataProvider(): array
    {
        return [
            [1_00_00, '1.0.0'],
            [1_10_00, '1.10.0'],
            [1_00_10, '1.0.10'],
            [1_10, '0.1.10'],
            [0, '0.0.0'],
            [1_00, '0.1.0'],
            [99_00, '0.99.0'],
        ];
    }

    #[DataProvider("dataProvider")]
    public function testValues(int $in, string $out)
    {
        $this->assertEquals($out, AppVersion::numericToString($in));
    }
}
