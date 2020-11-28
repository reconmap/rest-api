<?php

declare(strict_types=1);

namespace Reconmap\Controllers\System;

use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Controllers\Controller;
use Reconmap\Repositories\IntegrationsRepository;

class GetIntegrationsController extends Controller
{

    public function __invoke(ServerRequestInterface $request): array
    {
        $repository = new IntegrationsRepository($this->container);
        $integrations = $repository->findAll();

        return $integrations;
    }
}
