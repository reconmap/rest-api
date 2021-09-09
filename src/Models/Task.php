<?php declare(strict_types=1);

namespace Reconmap\Models;

class Task
{
    public ?int $id;
    public ?int $project_id;
    public ?int $creator_uid;
    public ?int $assignee_uid;
    public ?string $insert_ts;
    public ?string $update_ts;
    public string $summary;
    public ?string $description;
    public string $status;
    public ?string $due_date = null;
    public ?int $command_id;

    public ?string $command;
    public ?string $command_parser;
}
