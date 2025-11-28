<?php declare(strict_types=1);

namespace Reconmap\Services\QueryParams;

use Symfony\Component\DependencyInjection\Attribute\Exclude;

#[Exclude]
class OrderByRequestHandler
{
    private string $orderColumn;
    private string $orderDirection;

    public function __construct(array  $params,
                                string $defaultOrderColumn,
                                string $defaultOrderDirection = 'ASC',
                                ?array $validColumns = null
    )
    {
        if (isset($params['orderColumn'])) {
            $orderColumn = $params['orderColumn'];
            if (is_null($validColumns) || in_array($orderColumn, $validColumns)) {
                $this->orderColumn = $orderColumn;
            } else {
                throw new \InvalidArgumentException("Invalid order column: $orderColumn");
            }
        } else {
            $this->orderColumn = $defaultOrderColumn;
        }

        if (isset($params['orderDirection'])) {
            $orderDirection = strtolower($params['orderDirection']);
            if (in_array($orderDirection, ['asc', 'desc'])) {
                $this->orderDirection = $orderDirection;
            } else {
                throw new \InvalidArgumentException("Invalid order direction: $orderDirection");
            }
        } else {
            $this->orderDirection = $defaultOrderDirection;
        }
    }

    public function toSql(): string
    {
        return "$this->orderColumn $this->orderDirection";
    }
}
