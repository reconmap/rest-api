<?php declare(strict_types=1);

namespace Reconmap\Services;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class CvssTest extends TestCase
{

    public static function listSeverities(): array
    {
        return [
            [0, 'none'],
            [2, 'low'],
            [6.9, 'medium'],
            [7, 'high'],
            [100, 'critical']
        ];
    }

    #[DataProvider("listSeverities")]
    public function testToSeverity(float $score, string $severity): void
    {
        $cvss = new Cvss;
        $this->assertEquals($severity, $cvss->toSeverity($score));
    }
}
