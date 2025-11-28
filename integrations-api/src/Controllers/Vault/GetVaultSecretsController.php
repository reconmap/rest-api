<?php declare(strict_types=1);

namespace Reconmap\Controllers\Vault;

use OpenApi\Attributes as OpenApi;
use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Controllers\Controller;
use Reconmap\Http\Docs\Default200OkResponse;
use Reconmap\Http\Docs\Default403UnauthorisedResponse;
use Reconmap\Repositories\VaultRepository;

#[OpenApi\Get(
    path: "/vault",
    description: "Returns all secrets in the vault",
    security: ["bearerAuth"],
    tags: ["Vault"],
)]
#[Default200OkResponse]
#[Default403UnauthorisedResponse]
class GetVaultSecretsController extends Controller
{
    public function __construct(private readonly VaultRepository $repository)
    {
    }

    public function __invoke(ServerRequestInterface $request, array $args): array
    {
        $userId = $request->getAttribute('userId');
        return $this->repository->findByUserId($userId);
    }
}
