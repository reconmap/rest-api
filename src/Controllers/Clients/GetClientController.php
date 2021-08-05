<?php declare(strict_types=1);

namespace Reconmap\Controllers\Clients;

use Reconmap\Controllers\GetEntityController;
use Reconmap\Repositories\ClientRepository;

class GetClientController extends GetEntityController
{
    public function __construct(ClientRepository $repository)
    {
        parent::__construct($repository, 'clientId');
    }
}
