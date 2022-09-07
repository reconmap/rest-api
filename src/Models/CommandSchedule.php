<?php declare(strict_types=1);

namespace Reconmap\Models;

class CommandSchedule
{
    public ?int $id;
    public ?int $creator_uid;
    public ?int $command_id;
    public ?string $argument_values;
    public ?string $cron_expression;
}
