<?php declare(strict_types=1);

namespace Reconmap\Controllers\System;

use GuzzleHttp\Psr7\Utils;
use OpenApi\Annotations\OpenApi;
use OpenApi\Generator;
use Psr\Http\Message\ResponseInterface;
use Reconmap\Controllers\Controller;
use Reconmap\Services\ApplicationConfig;

class GetOpenApiYamlController extends Controller
{
    public function __construct(private readonly ApplicationConfig $applicationConfig)
    {
    }

    public function __invoke(): ResponseInterface
    {
        $generator = Generator::scan([$this->applicationConfig->getAppDir() . '/src'], ["version" => OpenApi::VERSION_3_0_0]);

        return $this->createOkResponse()
            ->withBody(Utils::streamFor($generator->toJson()));
    }
}
