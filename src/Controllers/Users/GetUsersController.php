<?php declare(strict_types=1);

namespace Reconmap\Controllers\Users;

use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Controllers\SecureController;
use Reconmap\Repositories\UserRepository;
use Reconmap\Services\Security\AuthorisationService;

class GetUsersController extends SecureController
{
    public function __construct(AuthorisationService $authorisationService,
                                private              readonly UserRepository $userRepository)
    {
        parent::__construct($authorisationService);
    }

    protected function getPermissionRequired(): string
    {
        return 'users.*';
    }

    public function process(ServerRequestInterface $request): array
    {
        return $this->userRepository->findAll();
    }
}
