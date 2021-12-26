<?php declare(strict_types=1);

namespace Reconmap\Models;

enum TaskPriority: string
{
    case Highest = 'highest';
    case High = 'high';
    case Medium = 'medium';
    case Low = 'low';
    case Lowest = 'lowest';
}
