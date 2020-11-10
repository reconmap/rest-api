<?php

declare(strict_types=1);

namespace Reconmap\Controllers\Users;

use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Controllers\Controller;
use Reconmap\Repositories\UserRepository;

class GetUsersController extends Controller
{

    public function __invoke(ServerRequestInterface $request): array
    {
        $userRepository = new UserRepository($this->db);
        $users = $userRepository->findAll();

        return $users;
    }
}
