<?php
declare(strict_types=1);

namespace Reconmap\Services;

use PHPUnit\Framework\TestCase;

class JwtPayloadCreatorTest extends TestCase
{
    public function testJwtBodyContainsBasicProperties()
    {
        $config = new Config([
            'jwt' => [
                'issuer' => 'me',
                'audience' => 'all of you'
            ]
        ]);

        $now = time();
        $user = ['id' => 104, 'role' => 'superadmin'];

        $subject = new JwtPayloadCreator($config);
        $payload = $subject->createFromUserArray($user);

        $this->assertEquals('me', $payload['iss']);
        $this->assertEquals('all of you', $payload['aud']);
        $this->assertEquals($user, $payload['data']);
        $this->assertTrue($payload['iat'] >= $now);
        $this->assertTrue($payload['nbf'] === $payload['iat']);
        $this->assertTrue($payload['exp'] === $payload['iat'] + 3600);
    }
}
