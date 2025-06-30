<?php declare(strict_types=1);

namespace Reconmap\Controllers\Notifications;

use GuzzleHttp\Psr7\Response;
use OpenApi\Attributes as OpenApi;
use Ponup\SqlBuilders\SearchCriteria;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Controllers\Controller;
use Reconmap\Http\Docs\Default200OkResponse;
use Reconmap\Http\Docs\Default403UnauthorisedResponse;
use Reconmap\Repositories\NotificationsRepository;

#[OpenApi\Get(
    path: "/notifications",
    description: "Returns all notifications",
    security: ["bearerAuth"],
    tags: ["Notifications"]
)]
#[Default200OkResponse]
#[Default403UnauthorisedResponse]
class GetNotificationsController extends Controller
{
    public function __construct(private NotificationsRepository $repository)
    {
    }

    public function __invoke(ServerRequestInterface $request): ResponseInterface
    {
        $user = $this->getUserFromRequest($request);
        $params = $request->getQueryParams();

        $searchCriteria = new SearchCriteria();
        $searchCriteria->addCriterion('n.to_user_id = ?', [$user->id]);
        if (isset($params['status'])) {
            $searchCriteria->addCriterion('n.status = ?', [$params['status']]);
        }

        $notifications = $this->repository->search($searchCriteria);

        $response = new Response;
        $response->getBody()->write(json_encode($notifications));
        return $response
            ->withHeader('Access-Control-Expose-Headers', 'X-Page-Count');
    }
}
