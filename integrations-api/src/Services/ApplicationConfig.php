<?php declare(strict_types=1);

namespace Reconmap\Services;

class ApplicationConfig implements \ArrayAccess
{
    static public function load(string $path): static
    {
        return new static(json_decode(file_get_contents($path), true));
    }

    public function __construct(private array $props = [])
    {
    }

    public function getSettings(string $name): mixed
    {
        if (!isset($this->props[$name])) {
            return null;
        }
        return $this->props[$name];
    }

    public function setAppDir(string $appDir): void
    {
        $this->props['appDir'] = $appDir;
    }

    public function getAppDir(): string
    {
        return $this->props['appDir'];
    }

    public function offsetExists(mixed $offset): bool
    {
        return isset($this->props[$offset]);
    }

    public function offsetGet(mixed $offset): mixed
    {
        return $this->getSettings($offset);
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        $this->props[$offset] = $value;
    }

    public function offsetUnset(mixed $offset): void
    {
        unset($this->props[$offset]);
    }


}
