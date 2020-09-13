<?php

declare(strict_types=1);

namespace Reconmap\Services;

class Cvss
{
    static public function toSeverity(float $score): string
    {
        switch (true) {
            case $score == 0:
                $severity = 'none';
                break;
            case $score >= 0.1 && $score <= 3.9:
                $severity = 'low';
                break;
            case $score >= 4.0 && $score <= 6.9:
                $severity = 'medium';
                break;
            case $score >= 7.0 && $score <= 8.9:
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
