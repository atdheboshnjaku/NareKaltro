<?php

use Fin\Narekaltro\App\Url;

require_once("../../vendor/autoload.php");


$action = Url::getParam('action');

switch($action) {
    
    case "add":
    require_once('role/add.php');
    break;
    
    case "edit":
    require_once('role/edit.php');
    break;

    case "remove":
    require_once('role/remove.php');
    break;
    
    default:
    require_once('role/list.php');

}







