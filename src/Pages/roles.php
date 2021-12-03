<?php

use Fin\Narekaltro\App\Url;

require_once("../../vendor/autoload.php");


$action = Url::getParam('action');

match ($action) {
    "add" => require_once('role/add.php'),
    "edit" => require_once('role/edit.php'),
    "remove" => require_once('role/remove.php'),
    default => require_once('role/list.php'),
};







