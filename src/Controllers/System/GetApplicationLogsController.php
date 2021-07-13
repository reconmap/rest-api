<?php declare(strict_types=1);

namespace Reconmap\Controllers\System;

use Fig\Http\Message\StatusCodeInterface;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\Stream;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Controllers\Controller;
use Reconmap\Services\ApplicationConfig;

class GetApplicationLogsController extends Controller
{
    public function __construct(private ApplicationConfig $applicationConfig)
    {
    }

    public function __invoke(ServerRequestInterface $request): ResponseInterface
    {
        $appDir = $this->applicationConfig->getAppDir();
        $pathName = implode(DIRECTORY_SEPARATOR, [$appDir, 'logs', 'application.log']);

        $response = (new Response)->withHeader('Content-type', 'text/plain');

        if (file_exists($pathName)) {
            $fd = fopen($pathName, 'r');
            return $response->withBody(new Stream($fd));
        }

        return $response->withStatus(StatusCodeInterface::STATUS_NO_CONTENT);
    }
}
