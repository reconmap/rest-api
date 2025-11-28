<?php declare(strict_types=1);


namespace Reconmap\Repositories;


interface Deletable
{
    public function deleteById(int $id): bool;
}
