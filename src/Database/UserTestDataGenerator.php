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
        $user->subject_id = 'xxxx';
        $user->email = 'su@localhost';
        $user->role = 'superuser';
        $this->userRepository->create($user);

        $user->id = 3;
        $user->full_name = 'Infosec pro';
        $user->username = 'user';
        $user->subject_id = 'xxxx';
        $user->email = 'user@localhost';
        $user->role = 'user';
        $this->userRepository->create($user);

        $user->id = 4;
        $user->full_name = 'Dear Customer';
        $user->username = 'cust';
        $user->subject_id = 'xxxx';
        $user->email = 'cust@localhost';
        $user->role = 'client';
        $this->userRepository->create($user);
    }
}
