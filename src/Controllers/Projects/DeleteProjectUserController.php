<?php declare(strict_types=1);

namespace Reconmap\Controllers\Projects;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Controllers\Controller;
use Reconmap\Repositories\ProjectUserRepository;

class DeleteProjectUserController extends Controller
{
    public function __construct(private readonly ProjectUserRepository $projectUserRepository)
    {
    }

    public function __invoke(ServerRequestInterface $request, array $args): ResponseInterface
    {
        $membershipId = (int)$args['membershipId'];

        $success = $this->projectUserRepository->deleteById($membershipId);

        return $success ? $this->createNoContentResponse() : $this->createInternalServerErrorResponse();
    }
}
