<?php declare(strict_types=1);

namespace Reconmap\Http\Docs;

use OpenApi\Attributes as OpenApi;

#[\Attribute(\Attribute::TARGET_CLASS | \Attribute::TARGET_METHOD | \Attribute::TARGET_PROPERTY | \Attribute::TARGET_PARAMETER | \Attribute::IS_REPEATABLE)]
class InPathIdParameter extends OpenApi\Parameter
{
    public function __construct(?string $name = null)
    {
        parent::__construct(name: $name, in: 'path', required: true, schema: new OpenApi\Schema(type: "integer"));
    }
}
