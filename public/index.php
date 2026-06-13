<?php

declare(strict_types=1);

use Fin\Narekaltro\Core\Request;

require dirname(__DIR__) . '/vendor/autoload.php';

$app = require dirname(__DIR__) . '/src/Bootstrap/app.php';

$app->handle(Request::capture())->send();
