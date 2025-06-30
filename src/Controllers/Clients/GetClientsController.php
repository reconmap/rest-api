<?php declare(strict_types=1);

namespace Reconmap\Controllers\Clients;

use OpenApi\Attributes as OpenApi;
use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Controllers\SecureController;
use Reconmap\Http\Docs\Default200OkResponse;
use Reconmap\Http\Docs\Default403UnauthorisedResponse;
use Reconmap\Repositories\ClientRepository;
use Reconmap\Services\Security\AuthorisationService;

#[OpenApi\Get(
    path: "/clients",
    description: "Returns all organisations",
    security: ["bearerAuth"],
    tags: ["Organisations"],
)]
#[Default200OkResponse]
#[Default403UnauthorisedResponse]
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
        $params = $request->getQueryParams();

        if (isset($params['kind'])) {
            return $this->repository->findByType($params['kind']);
        }
        return $this->repository->findAll();
    }
}
