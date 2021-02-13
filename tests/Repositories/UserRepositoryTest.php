<?php declare(strict_types=1);

namespace Reconmap\Repositories;

use Reconmap\DatabaseTestCase;

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
}
