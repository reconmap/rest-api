<?php declare(strict_types=1);

namespace Reconmap\Controllers\Targets;

use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Controllers\Controller;
use Reconmap\Repositories\TargetRepository;

class CreateTargetController extends Controller
{
    public function __construct(private TargetRepository $repository)
    {
    }

    public function __invoke(ServerRequestInterface $request, array $args): array
    {
        $target = $this->getJsonBodyDecoded($request);

        $result = $this->repository->insert((int)$target->projectId, $target->name, $target->kind);

        return ['success' => $result];
    }
}
