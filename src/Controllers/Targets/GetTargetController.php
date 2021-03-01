<?php declare(strict_types=1);

namespace Reconmap\Controllers\Targets;

use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Controllers\Controller;
use Reconmap\Repositories\TargetRepository;

class GetTargetController extends Controller
{
    public function __construct(private TargetRepository $repository)
    {
    }

    public function __invoke(ServerRequestInterface $request, array $args): array
    {
        $targetId = (int)$args['targetId'];

        return $this->repository->findById($targetId);
    }
}
