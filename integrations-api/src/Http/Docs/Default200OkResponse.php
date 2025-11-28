<?php declare(strict_types=1);

namespace Reconmap\Http\Docs;

use OpenApi\Attributes\Response as OpenApiResponse;
use Symfony\Component\HttpFoundation\Response;

#[\Attribute(\Attribute::TARGET_CLASS | \Attribute::TARGET_METHOD | \Attribute::IS_REPEATABLE)]
class Default200OkResponse extends OpenApiResponse
{
    public function __construct()
    {
        parent::__construct(response: Response::HTTP_OK, description: 'Request completed successfully.');
    }
}
