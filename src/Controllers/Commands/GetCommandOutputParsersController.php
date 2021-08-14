<?php declare(strict_types=1);

namespace Reconmap\Controllers\Commands;

use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Controllers\Controller;
use Reconmap\Processors\ProcessorFactory;

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
