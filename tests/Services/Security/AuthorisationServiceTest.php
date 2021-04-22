<?php declare(strict_types=1);

namespace Reconmap\Services\Security;

use PHPUnit\Framework\TestCase;

class AuthorisationServiceTest extends TestCase
{
    public function dataProvider(): array
    {
        return [
            [true, 'administrator', 'vulnerabilities.delete'],
            [true, 'administrator', 'projects.delete'],
            [true, 'administrator', 'users.delete'],
            [true, 'administrator', 'system.usage'],
            [true, 'superuser', 'vulnerabilities.delete'],
            [true, 'superuser', 'projects.delete'],
            [true, 'superuser', 'users.delete'],
            [false, 'superuser', 'system.usage'],
            [true, 'user', 'vulnerabilities.delete'],
            [true, 'user', 'projects.delete'],
            [false, 'user', 'users.delete'],
            [false, 'user', 'system.usage'],
            [false, 'client', 'vulnerabilities.delete'],
            [false, 'client', 'projects.delete'],
            [false, 'client', 'users.delete'],
            [false, 'client', 'system.usage'],
        ];
    }

    /**
     * @param bool $expected
     * @param string $role
     * @param string $action
     * @dataProvider dataProvider
     */
    public function testIfRoleIsAllowed(bool $expected, string $role, string $action)
    {
        $subject = new AuthorisationService();
        $this->assertEquals($expected, $subject->isRoleAllowed($role, $action));
    }
}
