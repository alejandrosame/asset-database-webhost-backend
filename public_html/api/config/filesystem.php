<?php

include_once '../libs/flysystem-1.x/src/ReadInterface.php';
include_once '../libs/flysystem-1.x/src/AdapterInterface.php';
include_once '../libs/flysystem-1.x/src/Adapter/AbstractAdapter.php';
include_once '../libs/flysystem-1.x/src/Adapter/Local.php';
include_once '../libs/flysystem-1.x/src/Plugin/PluggableTrait.php';
include_once '../libs/flysystem-1.x/src/Config.php';
include_once '../libs/flysystem-1.x/src/ConfigAwareTrait.php';
include_once '../libs/flysystem-1.x/src/FilesystemInterface.php';
include_once '../libs/flysystem-1.x/src/Util.php';
include_once '../libs/flysystem-1.x/src/Filesystem.php';
include_once '../libs/flysystem-1.x/src/FilesystemException.php';
include_once '../libs/flysystem-1.x/src/Exception.php';
include_once '../libs/flysystem-1.x/src/FileExistsException.php';
include_once '../libs/flysystem-1.x/src/condifg.php';
use League\Flysystem\Adapter\Local;
use League\Flysystem\Filesystem;

$adapter = new Local(__DIR__.'/../../uploads');
$filesystem = new Filesystem($adapter);
