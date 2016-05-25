<?php

require __DIR__ .'/../vendor/autoload.php';
require __DIR__ .'/../locale/locale.php';

$app = new \jakulov\SignUp\Application();
$app->run(\jakulov\SignUp\Http\Request::createFromGlobals());