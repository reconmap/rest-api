<?php declare(strict_types=1);

namespace Reconmap\Models;

class Notification
{
    public function __construct(public int     $toUserId,
                                public ?string $title,
                                public string  $content)
    {
    }
}
