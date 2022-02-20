<?php declare(strict_types=1);

namespace Reconmap\Controllers\Clients;

use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Controllers\Controller;
use Reconmap\Repositories\ClientRepository;

class GetClientsController extends Controller
{
    public function __construct(private readonly ClientRepository $repository)
    {
    }

    public function __invoke(ServerRequestInterface $request): array
    {
        return $this->repository->findAll();
    }
}
