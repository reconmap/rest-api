<?php declare(strict_types=1);

namespace Reconmap\Models;

/**
 * Autogenerated file, do not edit manually. @see https://github.com/reconmap/model-definitions
 */
class CommandSchedule {

	public ?int $id;
	public ?int $creator_uid;
	public ?int $command_id;
	public ?string $argument_values;
	public ?string $cron_expression;
}
