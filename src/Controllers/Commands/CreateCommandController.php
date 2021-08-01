<?php declare(strict_types=1);

namespace Reconmap\Controllers\Commands;

use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Controllers\Controller;
use Reconmap\Models\Command;
use Reconmap\Repositories\CommandRepository;

class CreateCommandController extends Controller
{
    public function __construct(private CommandRepository $repository)
    {
    }

    public function __invoke(ServerRequestInterface $request): array
    {
        $command = $this->getJsonBodyDecodedAsClass($request, new Command());
        $command->creator_uid = $request->getAttribute('userId');

        $result = $this->repository->insert($command);

        return ['success' => $result];
    }
}
