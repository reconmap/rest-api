<?php declare(strict_types=1);

namespace Reconmap\Models;

class Notification
{
    public function __construct(public ?string $title,
                                public string  $content)
    {
    }
}
