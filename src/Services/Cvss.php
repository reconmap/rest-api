<?php

declare(strict_types=1);

namespace Reconmap\Services;

class Cvss
{
    static private function betweenValue(float $value, float $start, float $end): bool
    {
        return $value >= $start && $value <= $end;
    }

    static public function toSeverity(float $score): string
    {
        switch (true) {
            case $score == 0:
                $severity = 'none';
                break;
            case self::betweenValue($score, 0.1, 3.9):
                $severity = 'low';
                break;
            case self::betweenValue($score, 4.0, 6.9):
                $severity = 'medium';
                break;
            case self::betweenValue($score, 7.0, 8.9):
                $severity = 'high';
                break;
            case $score >= 9.0:
                $severity = 'critical';
                break;
            default:
                throw new \InvalidArgumentException("Invalid CVSS score: $score");
        }
        
        return $severity;
    }
}
