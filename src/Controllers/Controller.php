<?php declare(strict_types=1);

namespace Reconmap\Controllers;

use Monolog\Logger;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Services\ContainerConsumer;
use Reconmap\Services\TemplateEngine;

abstract class Controller implements ContainerConsumer
{
    protected Logger $logger;
    protected \mysqli $db;
    protected TemplateEngine $template;
    protected ?ContainerInterface $container;

    public function __construct(Logger $logger, \mysqli $db, TemplateEngine $template)
    {
        $this->logger = $logger;
        $this->db = $db;
        $this->template = $template;
        $this->container = null;
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
