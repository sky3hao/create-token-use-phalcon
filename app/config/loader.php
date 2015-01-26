<?php

$loader = new \Phalcon\Loader();

$loader->registerDirs(
    array(
        $config->application->libraryDir
    )
)->setExtensions(array('php', 'class.php'))->register();
