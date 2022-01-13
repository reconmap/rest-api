<?php declare(strict_types=1);

namespace Reconmap\Controllers\Notifications;

use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Controllers\Controller;
use Reconmap\Repositories\NotificationsRepository;

class GetNotificationsController extends Controller
{
    public function __construct(private NotificationsRepository $repository)
    {
    }

    public function __invoke(ServerRequestInterface $request): ResponseInterface
    {
        $params = $request->getQueryParams();

        $notifications = $this->repository->findAll();

        $response = new Response;
        $response->getBody()->write(json_encode($notifications));
        return $response
            ->withHeader('Access-Control-Expose-Headers', 'X-Page-Count');

    }
}
