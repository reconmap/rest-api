<?php declare(strict_types=1);

namespace Reconmap\DomainObjects;

class Client extends \Reconmap\Models\Client
{
    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }
}
