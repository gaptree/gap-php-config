<?php
namespace Gap\Config;

class ConfigCollection
{
    protected $items = [];

    public function set($key, $val): self
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

    public function requireFile($file): self
    {
        $fileCollection = require $file;
        if ($fileCollection instanceof self) {
            foreach ($fileCollection->all() as $key => $val) {
                $this->set($key, $val);
            }
        }

        return $this;
    }

    public function requireDir($dir): self
    {
        if (!file_exists($dir)) {
            throw new \Exception('Cannot find dir: ' . $dir);
        }

        foreach (scandir($dir) as $file) {
            if (pathinfo($file, PATHINFO_EXTENSION) == 'php') {
                $this->requireFile($dir . '/' . $file);
            }
        }

        return $this;
    }
}
