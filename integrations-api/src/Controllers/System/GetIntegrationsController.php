<?php declare(strict_types=1);

namespace Reconmap\Controllers\System;

use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Controllers\Controller;
use Reconmap\Repositories\IntegrationsRepository;

class GetIntegrationsController extends Controller
{
    public function __construct(private readonly IntegrationsRepository $integrationsRepository)
    {
    }

    public function __invoke(ServerRequestInterface $request): array
    {
        return $this->integrationsRepository->findAll();
    }
}
