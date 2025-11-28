<?php declare(strict_types=1);

namespace Reconmap\Controllers\System\CustomFields;

use Override;
use Psr\Http\Message\ResponseInterface;
use Reconmap\Controllers\ControllerV2;
use Reconmap\Http\ApplicationRequest;
use Reconmap\Repositories\CustomFieldRepository;

class GetCustomFieldsController extends ControllerV2
{
    public function __construct(private readonly CustomFieldRepository $customFieldRepository)
    {
    }

    #[Override]
    protected function process(ApplicationRequest $request): ResponseInterface
    {
        $fields = $this->customFieldRepository->findAll();

        $response = $this->createOkResponse();
        $response->getBody()->write(json_encode($fields));

        return $response;
    }
}
