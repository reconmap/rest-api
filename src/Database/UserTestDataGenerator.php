<?php declare(strict_types=1);

namespace Reconmap\Database;

use Reconmap\Models\User;
use Reconmap\Repositories\UserRepository;

class UserTestDataGenerator
{
    public function __construct(private readonly UserRepository $userRepository)
    {
    }

    public function run(): void
    {
        $this->userRepository->updateById(1, [
            'full_name' => 'Jane Doe',
            'short_bio' => 'CEO and CTO of Amazing Pentest Company Limited'
        ]);

        $user = new User();
        $user->id = 2;
        $user->full_name = 'Lead pentester';
        $user->username = 'su';
        $user->password = '$2y$10$7u3qUhud4prBZdFVmODvXOCBuQBgq6MYHvZT7N74cMG/mnVBwiu7W';
        $user->email = 'su@localhost';
        $user->role = 'superuser';
        $this->userRepository->create($user);

        $user->id = 3;
        $user->full_name = 'Infosec pro';
        $user->username = 'user';
        $user->password = '$2y$10$pTgvYwR3Umwvb.cpIWw5kOpoqj49q.Q9tzHcRXcAnXdUaQe5C.Nom';
        $user->email = 'user@localhost';
        $user->role = 'user';
        $this->userRepository->create($user);

        $user->id = 4;
        $user->full_name = 'Dear Customer';
        $user->username = 'cust';
        $user->password = '$2y$10$/VVITsgw9ByDoCTCKTuBtemc44SoP4691aIVVyd/OgLblXQK6Tnwq';
        $user->email = 'cust@localhost';
        $user->role = 'client';
        $this->userRepository->create($user);
    }
}
