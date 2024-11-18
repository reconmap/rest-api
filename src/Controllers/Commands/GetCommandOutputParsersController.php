<?php declare(strict_types=1);

namespace Reconmap\Controllers\Commands;

use Psr\Http\Message\ServerRequestInterface;
use Reconmap\CommandOutputParsers\ProcessorFactory;
use Reconmap\Controllers\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;

class GetCommandOutputParsersController extends Controller
{
    public function __construct(private readonly ProcessorFactory $processorFactory)
    {
    }

    public function __invoke(ServerRequestInterface $request): JsonResponse
    {
        return new JsonResponse($this->processorFactory->getAll());
    }
}
