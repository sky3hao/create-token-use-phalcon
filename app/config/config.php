<?php

return new \Phalcon\Config(array(
    'database' => array(
        'adapter'     => 'Mysql',
        'host'        => 'localhost',
        'username'    => 'root',
        'password'    => '',
        'dbname'      => 'test',
    ),
    'application' => array(
        'libraryDir'     => __DIR__ . '/../../app/library/',
        'cacheDir'       => __DIR__ . '/../../app/cache/',
        'baseUri'        => '/tokens/',
    ),
    'memcache' => array(
        'host' => '168.192.1.102',
        'port' => 11211    
    ),
    'crypt' => array(
        'key' => '%^&$dsfg$%89892DF*&=>'
    ),
    'riak' => array(
        'host' => '168.192.1.102',
        'port' => 10027
    ),
));
