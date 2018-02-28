<?php
namespace Gap\Config;

class ConfigBuilder
{
    protected $config;

    protected $settingDir;
    protected $cacheFile;

    public function __construct(string $settingDir, string $cacheFile = '')
    {
        $this->settingDir = $settingDir;

        if (!is_dir($this->settingDir)) {
            throw new \Exception("Cannot find setting dir $settingDir");
        }
        $this->setCacheFile($cacheFile);
    }

    public function setCacheFile(string $cacheFile): void
    {
        $cacheDir = dirname($cacheFile);
        if (!is_dir($cacheDir)) {
            throw new \Exception("Cannot find cache dir $cacheDir");
        }
        $this->cacheFile = $cacheFile;
    }

    public function build()
    {
        $config = new Config();

        if (file_exists($this->cacheFile)) {
            $config->load(require $this->cacheFile);
            if (false === $config->bool('debug')) {
                return $config;
            }
            $config->clear();
        }

        $loader = new ConfigLoader();
        $loader->loadFile($this->settingDir . '/setting.local.php');
        $loader->loadFile($this->settingDir . '/setting.app.php');

        $loader->loadDir($this->settingDir . '/sys');
        $loader->loadDir($this->settingDir . '/enabled');
        $loader->loadDir($this->settingDir . '/local');

        $baseDir = $loader->get('baseDir');
        if (!file_exists($baseDir)) {
            throw new \Exception("Cannot find base dir $baseDir");
        }

        // todo up to now no one use app config
        foreach ($loader->get('app', []) as $opts) {
            if (!$dir = $opts['dir'] ?? false) {
                continue;
            }

            $dir = $dir[0] === '/' ? $dir : $baseDir . '/' . $dir;
            $configDir = $dir . '/setting/config';
            if (file_exists($configDir)) {
                $loader->loadDir($dir . '/setting/config');
            }
        }
        
        $config->load($loader->getConfigData());

        if (false === $config->bool('debug')) {
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
