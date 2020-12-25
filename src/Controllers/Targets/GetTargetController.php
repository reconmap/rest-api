<?php declare(strict_types=1);

namespace Reconmap\Controllers\Targets;

use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Controllers\Controller;
use Reconmap\Repositories\TargetRepository;

class GetTargetController extends Controller
{

    public function __invoke(ServerRequestInterface $request, array $args): array
    {
        $id = (int)$args['id'];

        $repository = new TargetRepository($this->db);
        return $repository->findById($id);
    }
}
