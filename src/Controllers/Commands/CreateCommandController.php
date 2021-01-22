<?php declare(strict_types=1);

namespace Reconmap\Controllers\Commands;

use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Controllers\Controller;
use Reconmap\Repositories\CommandRepository;

class CreateCommandController extends Controller
{
    public function __invoke(ServerRequestInterface $request, array $args): array
    {
        $command = $this->getJsonBodyDecoded($request);
        $command->creator_uid = $request->getAttribute('userId');

        $repository = new CommandRepository($this->db);
        $result = $repository->insert($command);

        return ['success' => $result];
    }
}
