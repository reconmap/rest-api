<?php declare(strict_types=1);

namespace Reconmap\Controllers\System;

use OpenApi\Annotations\OpenApi;
use OpenApi\Generator;
use Reconmap\Controllers\Controller;
use Reconmap\Services\ApplicationConfig;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

class GetOpenApiYamlController extends Controller
{
    public function __construct(private readonly ApplicationConfig $applicationConfig)
    {
    }

    #[Route('/openapi.json')]
    public function __invoke(): JsonResponse
    {
        $generator = Generator::scan([$this->applicationConfig->getAppDir() . '/src'], ["version" => OpenApi::VERSION_3_0_0]);

        return new JsonResponse($generator->toJson(), json: true);
    }
}
