<?php declare(strict_types=1);

namespace Reconmap\Controllers\Vault;

use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Controllers\Controller;
use Reconmap\Repositories\VaultRepository;

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
