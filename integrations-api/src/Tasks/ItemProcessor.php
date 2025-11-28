<?php declare(strict_types=1);

namespace Reconmap\Tasks;

interface ItemProcessor
{
    public function process(object $item): void;
}
