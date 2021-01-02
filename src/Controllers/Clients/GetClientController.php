<?php declare(strict_types=1);

namespace Reconmap\Controllers\Clients;

use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Controllers\Controller;
use Reconmap\Repositories\ClientRepository;

class GetClientController extends Controller
{
    public function __invoke(ServerRequestInterface $request, array $args): object
    {
        $id = (int)$args['clientId'];

        $repository = new ClientRepository($this->db);
        return $repository->findById($id);
    }
}
