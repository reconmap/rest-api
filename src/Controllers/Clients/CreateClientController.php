<?php declare(strict_types=1);

namespace Reconmap\Controllers\Clients;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Controllers\Controller;
use Reconmap\Models\Client;
use Reconmap\Repositories\ClientRepository;

class CreateClientController extends Controller
{
    public function __construct(private readonly ClientRepository $repository)
    {
    }

    public function __invoke(ServerRequestInterface $request): ResponseInterface
    {
        /** @var Client $client */
        $client = $this->getJsonBodyDecodedAsClass($request, new Client());
        $client->creator_uid = $request->getAttribute('userId');

        $client->id = $this->repository->insert($client);
        return $this->createStatusCreatedResponse($client);
    }
}
