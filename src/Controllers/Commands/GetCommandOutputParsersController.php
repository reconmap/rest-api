<?php declare(strict_types=1);

namespace Reconmap\Controllers\Commands;

use Psr\Http\Message\ServerRequestInterface;
use Reconmap\CommandOutputParsers\ProcessorFactory;
use Reconmap\Controllers\Controller;

class GetCommandOutputParsersController extends Controller
{
    public function __construct(private ProcessorFactory $processorFactory)
    {
    }

    public function __invoke(ServerRequestInterface $request): array
    {
        return $this->processorFactory->getAll();
    }
}
