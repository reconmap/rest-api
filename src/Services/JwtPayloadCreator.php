<?php declare(strict_types=1);

namespace Reconmap\Services;

class JwtPayloadCreator
{
    private ApplicationConfig $config;

    public function __construct(ApplicationConfig $config)
    {
        $this->config = $config;
    }

    public function createFromUserArray(array $user): array
    {
        $jwtConfig = $this->config->getSettings('jwt');

        $now = time();

        return [
            'iss' => $jwtConfig['issuer'],
            'aud' => $jwtConfig['audience'],
            'iat' => $now,
            'nbf' => $now,
            'exp' => $now + (60 * 60), // 1 hour
            'data' => [
                'id' => $user['id'],
                'role' => $user['role']
            ]
        ];
    }
}
