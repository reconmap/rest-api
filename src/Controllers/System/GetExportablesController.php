<?php declare(strict_types=1);

namespace Reconmap\Controllers\System;

use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Controllers\Controller;
use Reconmap\Repositories\Exporters\Exportables;

class GetExportablesController extends Controller
{
    public function __invoke(ServerRequestInterface $request): array
    {
        return array_map(fn(array $item) => ['key' => $item['key'], 'description' => $item['description']], Exportables::List);
    }
}
