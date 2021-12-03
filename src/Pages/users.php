<?php

use Fin\Narekaltro\App\Url;

require_once("../../vendor/autoload.php");


$action = Url::getParam('action');

match ($action) {
    "add" => require_once('user/add.php'),
    "edit" => require_once('user/edit.php'),
    "remove" => require_once('user/remove.php'),
    default => require_once('user/list.php'),
};







