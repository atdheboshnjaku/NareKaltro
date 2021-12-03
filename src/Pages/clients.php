<?php

use Fin\Narekaltro\App\Url;

require_once("../../vendor/autoload.php");


$action = Url::getParam('action');

match ($action) {
    "add" => require_once('client/add.php'),
    "edit" => require_once('client/edit.php'),
    "remove" => require_once('client/remove.php'),
    default => require_once('client/list.php'),
};







