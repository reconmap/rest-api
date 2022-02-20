<?php declare(strict_types=1);

namespace Reconmap\Models;

class Client
{
    public ?int $id;
    public int $creator_uid;
    public ?string $name;
    public ?string $address;
    public ?string $url;
    public ?string $contact_name;
    public ?string $contact_email;
    public ?string $contact_phone;
    public ?int $logo_attachment_id = null;
    public ?int $small_logo_attachment_id = null;

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }
}
