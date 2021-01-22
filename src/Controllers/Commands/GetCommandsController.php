<?php declare(strict_types=1);

namespace Reconmap\Controllers\Commands;

use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Controllers\Controller;
use Reconmap\Repositories\CommandRepository;

class GetCommandsController extends Controller
{
    public function __invoke(ServerRequestInterface $request, array $args): array
    {
        $repository = new CommandRepository($this->db);
        return $repository->findAll();
    }
}
