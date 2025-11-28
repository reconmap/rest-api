<?php declare(strict_types=1);

namespace Reconmap\Controllers\System;

use GuzzleHttp\Psr7\Utils;
use OpenApi\Annotations\OpenApi;
use OpenApi\Generator;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Controllers\Controller;
use Reconmap\Services\ApplicationConfig;

class GetOpenApiYamlController extends Controller
{
    public function __construct(private readonly ApplicationConfig $applicationConfig)
    {
    }

    public function __invoke(ServerRequestInterface $request, array $args): ResponseInterface
    {
        $generator = new Generator();
        $generator->setVersion(OpenApi::VERSION_3_1_0);

        return $this->createOkResponse()
            ->withBody(Utils::streamFor($generator->generate([$this->applicationConfig->getAppDir() . '/src'])->toJson()));
    }
}
