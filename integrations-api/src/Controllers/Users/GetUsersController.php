<?php declare(strict_types=1);

namespace Reconmap\Controllers\Users;

use OpenApi\Attributes as OpenApi;
use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Controllers\SecureController;
use Reconmap\Http\Docs\Default200OkResponse;
use Reconmap\Http\Docs\Default403UnauthorisedResponse;
use Reconmap\Repositories\UserRepository;
use Reconmap\Services\Security\AuthorisationService;

#[OpenApi\Get(
    path: "/users",
    description: "Returns all users",
    security: ["bearerAuth"],
    tags: ["Users"],
)]
#[Default200OkResponse]
#[Default403UnauthorisedResponse]
class GetUsersController extends SecureController
{
    public function __construct(AuthorisationService            $authorisationService,
                                private readonly UserRepository $userRepository)
    {
        parent::__construct($authorisationService);
    }

    protected function getPermissionRequired(): string
    {
        return 'users.*';
    }

    public function process(ServerRequestInterface $request, array $args): array
    {
        return $this->userRepository->findAll();
    }
}
