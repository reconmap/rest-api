<?php

declare(strict_types=1);

namespace Reconmap\Services;

class Cvss
{
    static public function toSeverity(float $score): string
    {
        switch (true) {
            case $score == 0:
                return 'none';
            case $score >= 0.1 && $score <= 3.9:
                return 'low';
            case $score >= 4.0 && $score <= 6.9:
                return 'medium';
            case $score >= 7.0 && $score <= 8.9:
                return 'high';
            case $score >= 9.0:
                return 'critica';
        }
    }
}
