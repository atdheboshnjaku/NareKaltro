<?php

use Fin\Narekaltro\App\Url;

require_once("../../vendor/autoload.php");


$action = Url::getParam('action');

match ($action) {
    "add" => require_once('location/add.php'),
    "edit" => require_once('location/edit.php'),
    "remove" => require_once('location/remove.php'),
    default => require_once('location/list.php'),
};







