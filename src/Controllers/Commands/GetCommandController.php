<?php

declare(strict_types=1);

namespace Reconmap\Controllers\Commands;

use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Controllers\Controller;
use Reconmap\Repositories\CommandRepository;

class GetCommandController extends Controller
{

    public function __invoke(ServerRequestInterface $request, array $args): array
    {
        $commandId = (int)$args['commandId'];

        $repository = new CommandRepository($this->db);
        return $repository->findById($commandId);
    }
}
