<?php declare(strict_types=1);

namespace Reconmap\Controllers\Clients;

use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Controllers\Controller;
use Reconmap\Models\Client;
use Reconmap\Repositories\ClientRepository;

class CreateClientController extends Controller
{
    public function __construct(private ClientRepository $repository)
    {
    }

    public function __invoke(ServerRequestInterface $request, array $args): array
    {
        /** @var Client $client */
        $client = $this->getJsonBodyDecoded($request);
        $client->creator_uid = $request->getAttribute('userId');

        $result = $this->repository->insert($client);

        return ['success' => $result];
    }
}
