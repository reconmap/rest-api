<?php declare(strict_types=1);

namespace Reconmap\Controllers\Commands;

use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Controllers\Controller;
use Reconmap\Repositories\CommandRepository;
use Reconmap\Repositories\CommandUsageRepository;

class DeleteCommandUsageController extends Controller
{
    public function __construct(private readonly CommandUsageRepository $repository)
    {
    }

    public function __invoke(ServerRequestInterface $request, array $args): array
    {
        $commandId = intval($args['commandId']);

        $success = $this->repository->deleteById($commandId);

        return ['success' => $success];
    }
}
