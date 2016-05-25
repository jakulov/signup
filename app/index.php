<?php

require __DIR__ .'/../vendor/autoload.php';
require __DIR__ .'/../locale/locale.php';

define('VAR_DIR', realpath(__DIR__ .'/../var'));
define('UPLOAD_DIR', realpath(__DIR__ .'/upload'));
define('UPLOAD_PATH', '/upload');

$app = new \jakulov\SignUp\Application();
$app->run(\jakulov\SignUp\Http\Request::createFromGlobals());