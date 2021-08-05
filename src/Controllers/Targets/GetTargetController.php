<?php declare(strict_types=1);

namespace Reconmap\Controllers\Targets;

use Reconmap\Controllers\GetEntityController;
use Reconmap\Repositories\TargetRepository;

class GetTargetController extends GetEntityController
{
    public function __construct(TargetRepository $repository)
    {
        parent::__construct($repository, 'targetId');
    }
}
