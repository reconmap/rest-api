<?php declare(strict_types=1);

namespace Reconmap\Controllers\System;

use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Controllers\Controller;
use Reconmap\Repositories\IntegrationsRepository;
use Reconmap\Services\ApplicationConfig;

class GetIntegrationsController extends Controller
{
    public function __construct(private ApplicationConfig $config)
    {
    }

    public function __invoke(ServerRequestInterface $request): array
    {
        $repository = new IntegrationsRepository($this->config);
        return $repository->findAll();
    }
}
