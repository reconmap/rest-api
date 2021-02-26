<?php declare(strict_types=1);

namespace Reconmap\Controllers\Commands;

use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Controllers\Controller;
use Reconmap\Repositories\CommandRepository;

class GetCommandController extends Controller
{
    public function __construct(private CommandRepository $repository)
    {
    }

    public function __invoke(ServerRequestInterface $request, array $args): array
    {
        $commandId = (int)$args['commandId'];

        return $this->repository->findById($commandId);
    }
}
