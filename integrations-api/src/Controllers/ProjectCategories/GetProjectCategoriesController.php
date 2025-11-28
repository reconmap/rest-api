<?php declare(strict_types=1);

namespace Reconmap\Controllers\ProjectCategories;

use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\ResponseInterface;
use Reconmap\Controllers\ControllerV2;
use Reconmap\Http\ApplicationRequest;
use Reconmap\Repositories\ProjectCategoriesRepository;

class GetProjectCategoriesController extends ControllerV2
{
    public function __construct(private readonly ProjectCategoriesRepository $projectRepository)
    {
    }

    protected function process(ApplicationRequest $request): ResponseInterface
    {
        $categories = $this->projectRepository->findAll();

        $response = new Response();
        $response->getBody()->write(json_encode($categories));

        return $response;
    }
}
