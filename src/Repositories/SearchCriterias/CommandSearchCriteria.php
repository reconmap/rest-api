<?php declare(strict_types=1);

namespace Reconmap\Repositories\SearchCriterias;

use Reconmap\Repositories\QueryBuilders\SearchCriteria;

class CommandSearchCriteria extends SearchCriteria
{
    public function addKeywordsCriterion(string $keywords)
    {
        $keywordsLike = "%$keywords%";
        $this->addCriterion('(c.name LIKE ? OR c.description LIKE ? OR JSON_CONTAINS(c.tags, ?))', [$keywordsLike, $keywordsLike, "\"$keywords\""]);
    }
}
