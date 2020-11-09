<?php

declare(strict_types=1);

namespace Reconmap\Services;

class JwtPayloadCreator
{
    public function createFromUserArray(array $user): array
    {
        $now = time();

        return [
            'iss' => 'reconmap.org',
            'aud' => 'reconmap.com',
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
