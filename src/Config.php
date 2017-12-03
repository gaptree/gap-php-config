<?php
namespace Gap\Config;

class Config implements \ArrayAccess
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

    public function loadCollection(ConfigCollection $collection)
    {
        foreach ($collection->all() as $key => $val) {
            $this->set($key, $val);
        }
    }

    public function has($key): bool
    {
        if (!isset($this->items[0]) || !$key) {
            return false;
        }

        $arr = $this->items;
        foreach (explode('.', $key) as $segment) {
            if (!$arr || !is_array($arr) || !array_key_exists($segment, $arr)) {
                return false;
            }
            $arr = $arr[$segment];
        }

        return true;
    }

    public function get($key, $default = '')
    {
        if (!$key) {
            return null;
        }

        $arr = $this->items;
        foreach (explode('.', $key) as $segment) {
            if (!$arr || !is_array($arr) || !array_key_exists($segment, $arr)) {
                return $this->value($default);
            }
            $arr = $arr[$segment];
        }

        return $this->value($arr);
    }

    public function getConfig($key): self
    {
        return new Config($this->get($key, null));
    }

    public function set($key, $val = null): self
    {
        if (is_array($key)) {
            foreach ($key as $subKey => $subVal) {
                $this->set($subKey, $subVal);
            }

            return $this;
        }

        if (is_string($key)) {
            if (is_array($val)) {
                foreach ($val as $subKey => $subVal) {
                    $this->set($key . '.' . $subKey, $subVal);
                }
                return $this;
            }

            $this->setItem($key, $val);
            return $this;
        }

        throw new \RuntimeException('config::set error format');
    }

    public function all(): array
    {
        return $this->items;
    }

    // implements ArrayAccess
    public function offsetExists($offset): bool
    {
        return $this->has($offset);
    }

    public function offsetGet($offset)
    {
        return $this->get($offset);
    }

    public function offsetSet($offset, $val): void
    {
        $this->set($offset, $val);
    }

    public function offsetUnset($offset): void
    {
        $this->set($offset, null);
    }

    public function clear(): void
    {
        unset($this->items);
    }

    // protected
    protected function setItem($key, $val)
    {
        if (!$key) {
            return;
        }

        if (is_string($val)) {
            $val = preg_replace_callback(
                '/%([^%]+)%/i',
                function ($match) {
                    return $this->get($match[1]);
                    /*
                    if ($res = $this->get($match[1])) {
                        return $res;
                    }

                    return $match[0];
                    */
                },
                $val
            );
        }


        $arr = &$this->items;
        $keys = explode('.', $key);
        while (isset($keys[1])) {
            $segment = array_shift($keys);
            if (!isset($arr[$segment]) || !is_array($arr[$segment])) {
                $arr[$segment] = [];
            }
            $arr = &$arr[$segment];
        }
        $arr[array_shift($keys)] = $val;
    }

    protected function value($val)
    {
        return $val instanceof \Closure ? $val() : $val;
    }
}
