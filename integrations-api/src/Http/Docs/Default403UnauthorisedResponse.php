<?php declare(strict_types=1);

namespace Reconmap\Http\Docs;

use OpenApi\Attributes\Response as OpenApiResponse;
use Symfony\Component\HttpFoundation\Response;

#[\Attribute(\Attribute::TARGET_CLASS | \Attribute::TARGET_METHOD | \Attribute::IS_REPEATABLE)]
class Default403UnauthorisedResponse extends OpenApiResponse
{
    public function __construct()
    {
        parent::__construct(response: Response::HTTP_UNAUTHORIZED, description: 'The user is not authorised to perform this action.');
    }
}
