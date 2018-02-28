# Gap Config

```php
<?php

use Gap\Config\ConfigBuilder;

$settingDir = '/your/setting/dir';
$configBuilder = new ConfigBuilder(
    __DIR__ . '/setting',
    $this->cacheFile
);

$config = $configBuilder->build();

$debug = $config->bool('debug'); // false

$dbDefaultConfg = $config->config('db')->config('default');

$dbDefaultConfig->str('driver');
$dbDefaultConfig->str('database');
$dbDefaultConfig->str('host');
$dbDefaultConfig->str('username');

$dbDefaultConfig->arr('username');
/*
[
    'driver' => 'mysql',
    'charset' => 'utf8mb4',
    'collation' => 'utf8mb4_unicode_ci',
    'database' => 'db',
    'host' => 'host',
    'username' => 'username',
    'password' => 'passwd'
]
*/
```

Structure of setting dir

- setting/
    - system/       system config
    - custom/       custom config, can overwrite system config
    - local/        local config, can overwrite custom and system config, ignored by git
    - setting.app.php
    - setting.local.php

Config loading sequence

1. setting.local.php (required)
2. setting.app.php (required)
3. system/
4. custom/
5. local/
