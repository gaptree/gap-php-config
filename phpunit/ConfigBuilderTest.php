<?php
namespace phpunit\Gap\Config;

use PHPUnit\Framework\TestCase;
use Gap\Config\ConfigBuilder;

class ConfigBuilderTest extends TestCase
{
    protected $cacheFile = __DIR__ . '/cache/test-config.php';

    public function testBuild(): void
    {
        $this->clearCache();

        $configBuilder = new ConfigBuilder(
            __DIR__ . '/setting',
            $this->cacheFile
        );

        $config = $configBuilder->build();

        $this->assertEquals(
            'www.gaptree.com',
            $config->config('site')->config('default')->str('host')
        );

        $this->assertEquals(
            'gap',
            $config->config('db')->config('default')->str('username')
        );

        $this->assertEquals(
            'www.gaptree.com',
            $config->config('session')->str('cookie_domain')
        );

        $this->assertFalse($config->bool('debug'));

        $cacheArr = require $this->cacheFile;
        $this->assertEquals(
            'www.gaptree.com',
            $cacheArr['session']['cookie_domain']
        );

        $this->clearCache();
    }

    protected function clearCache(): void
    {
        if (file_exists($this->cacheFile)) {
            unlink($this->cacheFile);
        }
    }
}
