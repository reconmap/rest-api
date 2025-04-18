<?php declare(strict_types=1);

namespace Reconmap\Models;

/**
 * Autogenerated file, do not edit manually. @see https://github.com/reconmap/model-definitions
 */
class Client {

	public ?int $id;
	public ?int $creator_uid;
	public string $kind;
	public ?string $insert_ts;
	public ?string $update_ts;
	public ?string $name;
	public ?string $address;
	public ?string $url;
	public ?int $logo_attachment_id = null;
	public ?int $small_logo_attachment_id = null;
}
