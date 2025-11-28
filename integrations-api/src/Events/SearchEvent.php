<?php

namespace Reconmap\Events;

use Symfony\Contracts\EventDispatcher\Event;

class SearchEvent extends Event
{
    public function __construct(private readonly int $userId, private readonly string $keywords)
    {
    }

    public function getUserId(): int
    {
        return $this->userId;
    }

    public function getKeywords(): string
    {
        return $this->keywords;
    }
}
