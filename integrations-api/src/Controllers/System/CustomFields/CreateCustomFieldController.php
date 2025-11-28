<?php declare(strict_types=1);

namespace Reconmap\Controllers\System\CustomFields;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Controllers\Controller;
use Reconmap\Models\CustomField;
use Reconmap\Repositories\CustomFieldRepository;

class CreateCustomFieldController extends Controller
{
    public function __construct(private readonly CustomFieldRepository $clientContactRepository)
    {
    }

    public function __invoke(ServerRequestInterface $request, array $args): ResponseInterface
    {
        /** @var CustomField $customField */
        $customField = $this->getJsonBodyDecodedAsClass($request, new CustomField());
        $this->clientContactRepository->insert($customField);

        return $this->createStatusCreatedResponse($customField);
    }
}
