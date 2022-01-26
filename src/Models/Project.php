<?php declare(strict_types=1);

namespace Reconmap\Models;

class Project
{
    public int $creator_uid;
    public ?int $client_id;
    public string $name;
    public ?string $description;
    public ?string $visibility = 'public';
    public bool $is_template = false;
    public ?string $engagement_type;
    public ?string $engagement_start_date;
    public ?string $engagement_end_date;
    public ?string $external_id;
}
