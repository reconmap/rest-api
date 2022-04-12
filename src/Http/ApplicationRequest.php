<?php declare(strict_types=1);

namespace Reconmap\Http;

use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Models\User;

class ApplicationRequest
{
    public function __construct(private readonly ServerRequestInterface $serverRequest)
    {
    }

    public function getUser(): User
    {
        $user = new User();
        $user->id = $this->serverRequest->getAttribute('userId');
        $user->role = $this->serverRequest->getAttribute('role');
        return $user;
    }
}
