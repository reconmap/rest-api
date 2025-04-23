<?php declare(strict_types=1);

namespace Reconmap\Http;

use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Models\User;
use Symfony\Component\DependencyInjection\Attribute\Exclude;

#[Exclude]
class ApplicationRequest
{
    private ?User $user = null;

    public function __construct(private readonly ServerRequestInterface $serverRequest, private readonly array $args)
    {
    }

    public function getArgs(): array
    {
        return $this->args;
    }

    public function getUser(): User
    {
        if (is_null($this->user)) {
            $this->user = new User();
            $this->user->id = $this->serverRequest->getAttribute('userId');
            $this->user->role = $this->serverRequest->getAttribute('role');
        }

        return $this->user;
    }
}
