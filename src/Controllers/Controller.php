<?php

declare(strict_types=1);

namespace Reconmap\Controllers;

use Firebase\JWT\JWT;
use League\Route\Http\Exception\ForbiddenException;
use Monolog\Logger;
use Psr\Http\Message\ServerRequestInterface;

abstract class Controller
{
	protected $logger;
	protected $db;
	protected $template;

	public function __construct(Logger $logger, \mysqli $db, \League\Plates\Engine $template)
	{
		$this->logger = $logger;
		$this->db = $db;
		$this->template = $template;
	}
}
