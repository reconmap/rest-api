<?php declare(strict_types=1);

namespace Reconmap\Controllers;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Http\ApplicationRequest;
use Reconmap\Services\Security\AuthorisationService;

abstract class ControllerV2 extends Controller
{
    public function __invoke(ServerRequestInterface $serverRequest, array $args): ResponseInterface
    {
        $applicationRequest = new ApplicationRequest($serverRequest, $args);
        $user = $applicationRequest->getUser();

        /** @var AuthorisationService $authorisationService */
        $authorisationService = new AuthorisationService();
        if (!$authorisationService->isRoleAllowed($user->role, $this->getPermissionRequired())) {
            return $this->createForbiddenResponse();
        }

        return $this->process($applicationRequest);
    }

    protected function getPermissionRequired(): string
    {
        return '*.*';
    }

    protected abstract function process(ApplicationRequest $request): ResponseInterface;
}
