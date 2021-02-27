<?php declare(strict_types=1);

namespace Reconmap\Controllers\Clients;

use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Controllers\Controller;
use Reconmap\Repositories\ClientRepository;

class GetClientController extends Controller
{
    public function __construct(private ClientRepository $repository)
    {
    }

    public function __invoke(ServerRequestInterface $request, array $args): object
    {
        $clientId = (int)$args['clientId'];

        return $this->repository->findById($clientId);
    }
}
