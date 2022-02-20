<?php declare(strict_types=1);

namespace Reconmap\Models;

class Client
{
    public ?int $id;
    public int $creator_uid;
    public ?string $name;
    public ?string $address;
    public ?string $url;

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }
}
