<?php declare(strict_types=1);

namespace Reconmap\Controllers;

use Firebase\JWT\JWT;
use League\Route\Http\Exception\ForbiddenException;
use Psr\Http\Message\ServerRequestInterface;

class Controller {

	// @todo replace with RSA keys
	const JWT_KEY = 'this is going to be replaced with asymmetric keys';

	protected $logger;

	public function __construct() {
		$this->logger = $GLOBALS['logger'];
	}

	protected function validateJwtToken(ServerRequestInterface $request) {
		$jwt = explode(' ', $request->getHeader('Authorization')[0])[1];
		$token = JWT::decode($jwt, self::JWT_KEY, ['HS256']);
		$this->logger->debug(var_export($token, true));

		if($token->iss !== 'reconmap.org') {
			throw new ForbiddenException();
		}
		if($token->aud !== 'reconmap.com') {
			throw new ForbiddenException();
		}

		$now = time();
		if($token->iat > $now) {
			throw new ForbiddenException();
		}
		if($now < $token->nbf) {
			throw new ForbiddenException();
		}
		if($now > $token->exp) {
			throw new ForbiddenException();
		}
	}	
}
