<?php
namespace Gap\Config;

class ConfigBuilder
{
    protected $config;

    protected $baseDir;
    protected $srcFile;
    protected $cacheFile;

    public function __construct($baseDir, $srcFile, $cacheFile)
    {
        $this->baseDir = $baseDir;
        $this->srcFile = $srcFile[0] === '/' ? $srcFile : $baseDir . '/' . $srcFile;
        $this->cacheFile = $cacheFile[0] === '/' ? $cacheFile : $baseDir . '/' . $cacheFile;
    }

    public function build()
    {
        $config = new Config();

        if (file_exists($this->cacheFile)) {
            $config->load(require $this->cacheFile);

            if (false === $config->get('debug')) {
                return $config;
            }

            $config->clear();
        }
        $collection = new ConfigCollection();
        $collection->set('baseDir', $this->baseDir);
        $collection->requireFile($this->srcFile);

        $config->loadCollection($collection);

        $appCollection = new ConfigCollection();
        foreach ($config->get('app', []) as $opts) {
            if (!$dir = $opts['dir'] ?? false) {
                continue;
            }

            $dir = $dir[0] === '/' ? $dir : $this->baseDir . '/' . $dir;
            $configDir = $dir . '/setting/config';
            if (file_exists($configDir)) {
                $appCollection->requireDir($dir . '/setting/config');
            }
        }
        $config->loadCollection($appCollection);

        if (false === $config->get('debug')) {
            $this->var2file($this->cacheFile, $config->all());
        }

        return $config;
    }

    protected function var2file(string $targetPath, $var): void
    {
        $writtern = file_put_contents(
            $targetPath,
            '<?php return ' . var_export($var, true) . ';'
        );

        if (false === $writtern) {
            throw new \Exception("Write content to file '$targetPath' failed");
        }
    }
}
