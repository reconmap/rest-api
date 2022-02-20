<?php declare(strict_types=1);

namespace Reconmap\Controllers\Clients;

use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Controllers\Controller;
use Reconmap\Repositories\ContactRepository;

class GetClientContactsController extends Controller
{
    public function __construct(private readonly ContactRepository $repository)
    {
    }

    public function __invoke(ServerRequestInterface $request, array $args): array
    {
        $clientId = intval($args['clientId']);

        return $this->repository->findByClientId($clientId);
    }
}
