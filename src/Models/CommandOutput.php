<?php declare(strict_types=1);

namespace Reconmap\Models;

class CommandOutput
{
    public ?int $id = null;
    public int $command_id;
    public int $submitted_by_uid;
    public string $file_name;
    public int $file_size;
    public ?string $file_mimetype;
    public string $file_content;
}

