<?php declare(strict_types=1);

namespace Reconmap\Models;

class Task
{
    public ?int $id;
    public ?int $project_id;
    public ?int $assignee_uid;
    public ?string $insert_ts;
    public ?string $update_ts;
    public string $name;
    public ?string $description;
    public boolean $completed;
    public ?string $command;
    public ?string $command_parser;
}
