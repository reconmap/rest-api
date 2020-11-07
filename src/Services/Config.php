<?php

declare(strict_types=1);

namespace Reconmap\Services;

class Config
{
    private array $settings;

    public function __construct(array $settings = [])
    {
        $this->settings = $settings;
    }

    public function update(string $name, $value): void
    {
        $this->settings[$name] = $value;
    }

    public function getSettings(string $name): array
    {
        return $this->settings[$name];
    }

    public function getSetting(string $name)
    {
        return $this->settings[$name];
    }

    public function getAppDir(): string
    {
        return $this->getSetting('appDir');
    }
}
