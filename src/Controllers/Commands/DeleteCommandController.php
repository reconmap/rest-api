<?php declare(strict_types=1);

namespace Reconmap\Controllers\Commands;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Controllers\Controller;
use Reconmap\Repositories\CommandRepository;

class DeleteCommandController extends Controller
{
    public function __construct(private readonly CommandRepository $repository)
    {
    }

    public function __invoke(ServerRequestInterface $request, array $args): ResponseInterface
    {
        $commandId = intval($args['commandId']);

        $success = $this->repository->deleteById($commandId);

        return $success ? $this->createNoContentResponse() : $this->createInternalServerErrorResponse();
    }
}
