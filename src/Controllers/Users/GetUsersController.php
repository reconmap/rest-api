<?php declare(strict_types=1);

namespace Reconmap\Controllers\Users;

use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Controllers\Controller;
use Reconmap\Repositories\UserRepository;

class GetUsersController extends Controller
{
    public function __construct(private UserRepository $userRepository)
    {
    }

    public function __invoke(ServerRequestInterface $request): array
    {
        return $this->userRepository->findAll();
    }
}
