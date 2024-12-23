<?php declare(strict_types=1);

namespace Reconmap\Models;

/**
 * Autogenerated file, do not edit manually. @see https://github.com/reconmap/model-definitions
 */
class Target {

	public int $project_id;
	public ?int $parent_id = null;
	public string $name;
	public string $kind;
	public ?string $tags;
}