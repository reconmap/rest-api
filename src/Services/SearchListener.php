<?php

namespace Reconmap\Services;

use Reconmap\Events\SearchEvent;

readonly class SearchListener
{
    public function __construct(private RedisServer $redisServer)
    {

    }

    public function __invoke(SearchEvent $searchEvent): void
    {
        $setName = "recent-searches-user{$searchEvent->getUserId()}";
        $this->redisServer->zAdd($setName, time(), $searchEvent->getKeywords());
    }
}
