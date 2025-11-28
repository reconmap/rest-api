<?php declare(strict_types=1);

namespace Reconmap\Controllers\Users;

use OpenApi\Attributes as OpenApi;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Controllers\Controller;
use Reconmap\Http\Docs\Default200OkResponse;
use Reconmap\Http\Docs\Default403UnauthorisedResponse;
use Reconmap\Http\Docs\InPathIdParameter;
use Reconmap\Repositories\UserRepository;

#[OpenApi\Get(
    path: "/users/{userId}",
    description: "Returns information about the user with the given id",
    security: ["bearerAuth"],
    tags: ["Users"],
    parameters: [new InPathIdParameter("userId")])
]
#[Default200OkResponse]
#[Default403UnauthorisedResponse]
class GetUserController extends Controller
{
    public function __construct(private readonly UserRepository $userRepository)
    {
    }

    public function __invoke(ServerRequestInterface $request, array $args): array|ResponseInterface
    {
        $userId = (int)$args['userId'];

        $user = $this->userRepository->findById($userId);

        if (null === $user) {
            return $this->createNotFoundResponse();
        }

        if (is_string($user['preferences']) && json_validate($user['preferences'])) {
            $user['preferences'] = json_decode($user['preferences'], true);
        } else {
            $this->logger->warning("Invalid user preferences provided");
        }

        return $user;
    }
}
