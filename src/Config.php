<?php
namespace Gap\Config;

class Config
{
    protected $items = [];

    public function __construct(array $items = [])
    {
        if ($items) {
            $this->load($items);
        }
    }

    public function load(array $items): void
    {
        $this->items = array_merge_recursive($this->items, $items);
    }

    public function has(string $key): bool
    {
        return array_key_exists($key, $this->items);
    }

    public function str(string $key, string $default = ''): string
    {
        return $this->items[$key] ?? $default;
    }

    public function arr(string $key, array $default = []): array
    {
        return $this->items[$key] ?? $default;
    }

    public function int(string $key, int $default = 0): int
    {
        return $this->items[$key] ?? $default;
    }

    public function bool(string $key): bool
    {
        return $this->items[$key] ?? false;
    }

    public function config(string $key): Config
    {
        return new Config($this->arr($key));
    }

    public function all(): array
    {
        return $this->items;
    }

    public function clear(): void
    {
        unset($this->items);
    }
}
