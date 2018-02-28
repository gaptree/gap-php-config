<?php
$collection = new \Gap\Config\ConfigCollection();

$collection
    ->set('debug', false)
    ->set('baseHost', 'gaptree.com')
    ->set('baseDir', realpath(__DIR__ . '/../'))
    ->set('local', [
        'db' => [
            'host' => 'db',
            'database' => 'gap',
            'username' => 'gap',
            'password' => '123456789'
        ],
        'cache' => [
            'host' => 'redis'
        ],
        'session' => [
            'save_handler' => 'redis',
            'save_path' => 'tcp://redis:6379?database=10',
            'subdomain' => 'www'
        ]
    ]);

return $collection;
