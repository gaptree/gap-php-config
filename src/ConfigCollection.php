<?php
namespace Gap\Config;

class ConfigCollection
{
    protected $items = [];

    public function set(string $key, $val): self
    {
        if (isset($this->items[$key]) && is_array($this->items[$key]) && is_array($val)) {
            $this->items[$key] = array_merge(
                $this->items[$key],
                $val
            );
            return $this;
        }

        $this->items[$key] = $val;
        return $this;
    }

    public function all(): array
    {
        return $this->items;
    }
}
