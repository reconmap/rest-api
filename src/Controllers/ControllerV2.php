<?php declare(strict_types=1);

namespace Reconmap\Controllers;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Http\ApplicationRequest;

abstract class ControllerV2 extends Controller
{
    public function __invoke(ServerRequestInterface $serverRequest): ResponseInterface
    {
        return $this->process(new ApplicationRequest($serverRequest));
    }

    protected abstract function process(ApplicationRequest $request): ResponseInterface;
}
