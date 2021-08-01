<?php declare(strict_types=1);

namespace Reconmap\Models;

class Command
{
    public ?int $id;
    public ?int $creator_uid;
    public string $short_name;
    public ?string $description;
    public ?string $executable_type;
    public ?string $executable_path;
    public ?string $docker_image;
    public ?string $arguments;
    public ?string $output_filename;
    public ?string $more_info_url;
    public ?string $tags;
}
