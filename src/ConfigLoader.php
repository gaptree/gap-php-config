<?php
namespace Gap\Config;

class ConfigLoader
{
    protected $items;

    public function set(string $key, $val): self
    {
        if (is_array($val)) {
            foreach ($val as $subKey => $subVal) {
                $this->set($key . '.' . $subKey, $subVal);
            }
            return $this;
        }

        $this->setItem($key, $val);
        return $this;
    }

    public function get(string $key, $default = '')
    {
        return $this->items[$key] ?? $default;
    }

    public function loadCollection(ConfigCollection $collection): void
    {
        foreach ($collection->all() as $key => $val) {
            $this->set($key, $val);
        }
    }

    public function loadFile(string $file): self
    {
        if (!file_exists($file)) {
            throw new \Exception("Cannot find file: $file");
        }
        try {
            $this->loadCollection(require $file);
        } catch (\TypeError $e) {
            throw new \Exception(
                "Load config collection from file '$file' failed: \n"
                . $e->getMessage()
            );
        }
        return $this;
    }

    public function loadDir(string $dir): self
    {
        if (!is_dir($dir)) {
            throw new \Exception("Cannot find dir: $dir");
        }

        try {
            foreach (scandir($dir) as $file) {
                if (pathinfo($file, PATHINFO_EXTENSION) === 'php') {
                    $this->loadFile($dir . '/' . $file);
                }
            }
        } catch (\Exception $e) {
            throw new \Exception(
                "Load config collection from dir '$dir' failed: \n"
                . $e->getMessage()
            );
        }

        return $this;
    }

    public function getConfigData(): array
    {
        $this->pregReplace($this->items);
        return $this->items;
    }

    protected function pregReplace(array &$arr): void
    {
        foreach ($arr as $key => &$val) {
            if (is_string($val)) {
                $arr[$key] = preg_replace_callback(
                    '/%([^%]+)%/i',
                    function ($match) {
                        return $this->getItem($match[1], '');
                    },
                    $val
                );
            }

            if (is_array($val)) {
                $this->pregReplace($val);
            }
        }
    }

    protected function setItem(string $key, $val): void
    {
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

    protected function getItem(string $key, $default = '')
    {
        if (!$key) {
            return null;
        }

        $res = $this->items;
        foreach (explode('.', $key) as $segment) {
            if (!$res || !is_array($res) || !array_key_exists($segment, $res)) {
                return $default;
            }
            $res = $res[$segment];
        }

        return $res;
    }
}
