<?php declare(strict_types=1);

namespace Reconmap\Models;

class Organisation
{
    public ?int $id = null;
    public string $name;
    public ?string $url = null;
    public ?string $contact_name = null;
    public ?string $contact_email = null;
    public ?string $contact_phone = null;
    public ?int $logo_attachment_id = null;
    public ?int $small_logo_attachment_id = null;
}
