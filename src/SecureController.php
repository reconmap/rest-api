<?php declare(strict_types=1);

namespace Reconmap;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Controllers\Controller;
use Reconmap\Services\Security\AuthorisationService;

abstract class SecureController extends Controller
{
    public function __construct(private readonly AuthorisationService $authorisationService)
    {
    }

    public function __invoke(ServerRequestInterface $request): array|ResponseInterface
    {
        $role = $request->getAttribute('role');
        if (!$this->authorisationService->isRoleAllowed($role, $this->getPermissionRequired())) {
            return $this->createForbiddenResponse();
        }

        return $this->process($request);
    }

    protected function getPermissionRequired(): string
    {
        return '*.*';
    }

    protected abstract function process(ServerRequestInterface $request): array|ResponseInterface;
}
