<?php declare(strict_types=1);

namespace Reconmap\Controllers;

use Monolog\Logger;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Services\ContainerConsumer;
use Reconmap\Services\TemplateEngine;

abstract class Controller implements ContainerConsumer
{
    protected ?Logger $logger = null;
    protected ?\mysqli $db = null;
    protected ?TemplateEngine $template = null;
    protected ?ContainerInterface $container = null;

    public function setLogger(Logger $logger): void
    {
        $this->logger = $logger;
    }

    public function setDb(\mysqli $db): void
    {
        $this->db = $db;
    }

    public function setTemplate(TemplateEngine $template): void
    {
        $this->template = $template;
    }

    public function setContainer(ContainerInterface $container): void
    {
        $this->container = $container;
    }

    public function getJsonBodyDecoded(ServerRequestInterface $request): object
    {
        return json_decode((string)$request->getBody());
    }

    public function getJsonBodyDecodedAsArray(ServerRequestInterface $request): array
    {
        return json_decode((string)$request->getBody(), true);
    }
}
