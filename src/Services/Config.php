<?php

declare(strict_types=1);

namespace Reconmap\Services;

class Config
{

    private $settings;

    public function __construct(string $path)
    {
        $this->settings = json_decode(file_get_contents($path), true);
    }

    public function getSettings(string $name): array
    {
        return $this->settings[$name];
    }
}
