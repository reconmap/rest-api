<?php declare(strict_types=1);

namespace Reconmap\Services\Security;

class Permissions
{
    public const array ByRoles = [
        'administrator' => [
            '*.*'
        ],
        'superuser' => [
            'vulnerabilities.*',
            'commands.*',
            'tasks.*',
            'projects.*',
            'users.*',
            'clients.*',
        ],
        'user' => [
            'vulnerabilities.*',
            'commands.*',
            'tasks.*',
            'projects.*',
            'users.login',
        ],
        'client' => [
            'projects.list',
            'users.login',
        ]
    ];
}
