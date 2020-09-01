<?php

declare(strict_types=1);

namespace Reconmap\Repositories;

use PHPUnit\Framework\TestCase;

class UserRepositoryTest extends TestCase
{
    private UserRepository $subject;

    public function setUp(): void
    {
        $db = new \mysqli('db', 'reconmapper', 'reconmapped', 'reconmap');
        $this->subject = new UserRepository($db);
    }

    public function testFindAllReturnsAllRecords()
    {
        $users = $this->subject->findAll();
        $this->assertCount(5, $users);
    }

    public function testFindByValidIdReturnsUser()
    {
        $user = $this->subject->findById(4);
        $this->assertEquals('writer3', $user['name']);
    }

    public function testFindByInvalidIdReturnsNull()
    {
        $user = $this->subject->findById(0);
        $this->assertNull($user);
    }
}
