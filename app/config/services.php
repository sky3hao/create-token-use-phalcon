<?php

use Phalcon\DI\FactoryDefault,
    Phalcon\Cache\Frontend\Data as FrontendData,
    Phalcon\Cache\Backend\Riak,
    Phalcon\Cache\Backend\Memcache as BkMemcache,
    Phalcon\Queue\Beanstalk\Extended as BeanstalkExtended;

$di = new FactoryDefault();

$di->set('cache', function () use ($config) {
    $frontCache = new FrontendData();
    $cache = new BkMemcache($frontCache, array(
        'host' => $config->memcache->host,
        'port' => $config->memcache->port
    ));

    return $cache;
});

$di->set('crypt', function () use ($config) {
    $crypt = new Phalcon\Crypt();
    $crypt->setKey($config->crypt->key);

    return $crypt;
}, true);

$di->set('tokenBk', function () use ($config) {
    $frontCache = new FrontendData();
    $riak = new Riak($frontCache, array(
        'host'   => $config->riak->host,
        'port'   => $config->riak->port,
        'bucket' => 'token'
    ));

    return $riak;
}, true);
