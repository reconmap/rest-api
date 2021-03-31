<?php declare(strict_types=1);

namespace Reconmap\Repositories;

use Reconmap\DatabaseTestCase;
use Reconmap\Models\User;

class UserRepositoryTest extends DatabaseTestCase
{
    private UserRepository $subject;

    public function setUp(): void
    {
        $db = $this->getDatabaseConnection();
        $this->subject = new UserRepository($db);
    }

    public function testFindAllReturnsAllRecords()
    {
        $users = $this->subject->findAll();
        $this->assertCount(4, $users);
    }

    public function testFindByValidIdReturnsUser()
    {
        $user = $this->subject->findById(4);
        $this->assertEquals('cust', $user['username']);
    }

    public function testFindByUsernameReturnsUser()
    {
        $user = $this->subject->findByUsername('admin');
        $this->assertEquals('administrator', $user['role']);
    }

    public function testFindByInvalidIdReturnsNull()
    {
        $user = $this->subject->findById(0);
        $this->assertNull($user);
    }

    public function testInsert()
    {
        $user = new User();
        $user->full_name = 'Mr Happy';
        $user->username = 'mrhappy';
        $user->password = 'Password123';
        $user->email = 'mr.happy@emailprovider.internet';
        $user->role = 'administrator';

        $this->assertEquals(5, $this->subject->create($user));
    }
}
