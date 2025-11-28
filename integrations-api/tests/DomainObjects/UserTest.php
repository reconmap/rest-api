<?php declare(strict_types=1);

namespace Reconmap\DomainObjects;

use PHPUnit\Framework\TestCase;

class UserTest extends TestCase
{
    public function testIsAdministrator()
    {
        $user = new User();
        $user->role = 'administrator';
        $this->assertTrue($user->isAdministrator());
    }

    public function testIsNotAdministrator()
    {
        $user = new User();
        $this->assertFalse($user->isAdministrator());
    }
}
