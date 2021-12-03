<?php

use Fin\Narekaltro\App\Url;

require_once("../../vendor/autoload.php");


$action = Url::getParam('action');

match ($action) {
    "add" => require_once('service/add.php'),
    "edit" => require_once('service/edit.php'),
    "remove" => require_once('service/remove.php'),
    default => require_once('service/list.php'),
};