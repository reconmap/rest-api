<?php declare(strict_types=1);

namespace Reconmap\Models;

class Attachment
{
    public ?int $id = null;
    public int|string|null $insert_ts = null;

    public string $parent_type;
    public int $parent_id;
    public int $submitter_uid;
    public string $client_file_name;
    public string $file_name;
    public int $file_size;
    public ?string $file_mimetype;
    public string $file_hash;
}
