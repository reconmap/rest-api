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

	public function __construct(Logger $logger, \mysqli $db)
	{
		$this->logger = $logger;
		$this->db = $db;
	}
}
