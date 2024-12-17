<?php declare(strict_types=1);

namespace Reconmap\Controllers\Clients;

use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Controllers\SecureController;
use Reconmap\Repositories\ClientRepository;
use Reconmap\Services\Security\AuthorisationService;

class GetClientsController extends SecureController
{
    public function __construct(AuthorisationService              $authorisationService,
                                private readonly ClientRepository $repository)
    {
        parent::__construct($authorisationService);
    }

    protected function getPermissionRequired(): string
    {
        return 'clients.*';
    }

    public function process(ServerRequestInterface $request, array $args): array
    {
        return $this->repository->findAll();
    }
}
