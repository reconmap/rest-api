<?php declare(strict_types=1);

namespace Reconmap\Controllers\Commands;

use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Controllers\Controller;
use Reconmap\Repositories\CommandRepository;

class CreateCommandController extends Controller
{
    public function __construct(private CommandRepository $repository)
    {
    }

    public function __invoke(ServerRequestInterface $request, array $args): array
    {
        $command = $this->getJsonBodyDecoded($request);
        $command->creator_uid = $request->getAttribute('userId');

        $result = $this->repository->insert($command);

        return ['success' => $result];
    }
}
