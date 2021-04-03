<?php declare(strict_types=1);

namespace Reconmap\Services;

class JwtPayloadCreator
{
    public function __construct(private ApplicationConfig $config)
    {
    }

    public function createFromUserArray(array $user, bool $shortLived = false): array
    {
        $jwtConfig = $this->config->getSettings('jwt');

        $now = time();
        $expirationTime = $now + ($shortLived ? 5 * 60 : 60 * 60);

        return [
            'iss' => $jwtConfig['issuer'],
            'aud' => $jwtConfig['audience'],
            'iat' => $now,
            'nbf' => $now,
            'exp' => $expirationTime,
            'data' => [
                'id' => $user['id'],
                'role' => $user['role'],
                'mfa' => $user['mfa']
            ]
        ];
    }
}
