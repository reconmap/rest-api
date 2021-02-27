<?php declare(strict_types=1);

namespace Reconmap\Services;

class Cvss
{
    static public function toSeverity(float $score): string
    {
        return match (true) {
            $score == 0 => 'none',
            self::betweenValue($score, 0.1, 3.9) => 'low',
            self::betweenValue($score, 4.0, 6.9) => 'medium',
            self::betweenValue($score, 7.0, 8.9) => 'high',
            $score >= 9.0 => 'critical',
            default => throw new \InvalidArgumentException("Invalid CVSS score: $score")
        };
    }

    static private function betweenValue(float $value, float $start, float $end): bool
    {
        return $value >= $start && $value <= $end;
    }
}
