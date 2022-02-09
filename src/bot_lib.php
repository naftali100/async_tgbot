<?php

require_once __DIR__.'/../vendor/autoload.php';

$class_list = ['config', 'http', 'api', 'update', 'handler', 'loader', 'helpers', 'server', 'filter', 'file'];
foreach ($class_list as $file) {
    $file = __DIR__ . '/' . $file . '.php';
    require_once $file;
}
