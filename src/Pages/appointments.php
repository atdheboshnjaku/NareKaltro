<?php

use Fin\Narekaltro\App\Url;

require_once("../../vendor/autoload.php");


$action = Url::getParam('action');

match ($action) {
    "add" => require_once('appointment/add.php'),
    "edit" => require_once('appointment/edit.php'),
    "remove" => require_once('appointment/remove.php'),
    default => require_once('appointment/list.php'),
};






