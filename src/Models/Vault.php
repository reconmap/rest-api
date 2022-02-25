<?php declare(strict_types=1);

namespace Reconmap\Models;

class Vault
{
    public ?int $id = null;
    public ?string $insert_ts = null;
    public ?string $update_ts = null;
    public ?string $name = null;
    public ?string $value = null;
    public ?bool $reportable = false;
    public ?string $note = null;
    public string $type = 'password'; // password, note, token, key
    public ?int $project_id = null;
}

