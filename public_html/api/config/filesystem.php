<?php
require __DIR__ . '/../../../vendor/autoload.php';
use League\Flysystem\Adapter\Local;
use League\Flysystem\Filesystem;

$adapter = new Local(__DIR__.'/../../uploads');
$filesystem = new Filesystem($adapter);
