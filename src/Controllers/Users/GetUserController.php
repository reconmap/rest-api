<?php declare(strict_types=1);

namespace Reconmap\Controllers\Users;

use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Controllers\Controller;
use Reconmap\Repositories\UserRepository;

class GetUserController extends Controller
{
    public function __construct(private readonly UserRepository $userRepository)
    {
    }

    public function __invoke(ServerRequestInterface $request, array $args): array
    {
        $userId = (int)$args['userId'];

        $user = $this->userRepository->findById($userId);
        $user['preferences'] = json_decode($user['preferences'], true);

        return $user;
    }
}
