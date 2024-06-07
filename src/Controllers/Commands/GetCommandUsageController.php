<?php declare(strict_types=1);

namespace Reconmap\Controllers\Commands;

use League\Route\Http\Exception\NotFoundException;
use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Controllers\Controller;
use Reconmap\Repositories\CommandRepository;
use Reconmap\Repositories\CommandUsageRepository;

class GetCommandUsageController extends Controller
{
    public function __construct(private readonly CommandUsageRepository $repository)
    {
    }

    public function __invoke(ServerRequestInterface $request, array $args): array
    {
        $commandId = (int)$args['commandId'];

        $command = $this->repository->findById($commandId);
        if (is_null($command)) {
            throw new NotFoundException();
        }

        return $command;
    }
}
