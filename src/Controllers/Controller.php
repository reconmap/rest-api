<?php

declare(strict_types=1);

namespace Reconmap\Controllers;

use League\Plates\Engine;
use Monolog\Logger;
use Psr\Container\ContainerInterface;
use Reconmap\Services\ContainerConsumer;

abstract class Controller implements ContainerConsumer
{
	protected Logger $logger;
	protected \mysqli $db;
	protected Engine $template;
	protected ?ContainerInterface $container;

	public function __construct(Logger $logger, \mysqli $db, Engine $template)
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
}
