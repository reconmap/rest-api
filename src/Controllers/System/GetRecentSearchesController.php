<?php declare(strict_types=1);

namespace Reconmap\Controllers\System;

use Psr\Http\Message\ResponseInterface;
use Reconmap\Controllers\ControllerV2;
use Reconmap\Http\ApplicationRequest;
use Reconmap\Services\RedisServer;

class GetRecentSearchesController extends ControllerV2
{
    public function __construct(private readonly RedisServer $redisServer)
    {
    }

    #[\Override]
    protected function process(ApplicationRequest $request): ResponseInterface
    {
        $userId = $request->getUser()->id;
        $setName = "recent-searches-user{$userId}";

        $recentSearches = $this->redisServer->zRange($setName, 0, 10);

        $response = $this->createOkResponse();
        $response->getBody()->write(json_encode($recentSearches));

        return $response;
    }
}
