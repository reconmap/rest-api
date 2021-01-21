<?php declare(strict_types=1);

namespace Reconmap\Models;

class Client
{
    public ?int $id;
    public int $creator_uid;
    public ?string $name;
    public ?string $url;
    public ?string $contactName;
    public ?string $contactEmail;
    public ?string $contactPhone;

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }
}
