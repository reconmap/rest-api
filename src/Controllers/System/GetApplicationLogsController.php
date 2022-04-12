<?php declare(strict_types=1);

namespace Reconmap\Controllers\System;

use Fig\Http\Message\StatusCodeInterface;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\Stream;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Controllers\SecureController;
use Reconmap\Services\ApplicationConfig;
use Reconmap\Services\Security\AuthorisationService;

class GetApplicationLogsController extends SecureController
{
    public function __construct(AuthorisationService $authorisationService,
                                private              readonly ApplicationConfig $applicationConfig)
    {
        parent::__construct($authorisationService);
    }

    public function process(ServerRequestInterface $request): array|ResponseInterface
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
