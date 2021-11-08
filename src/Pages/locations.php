<?php

use Fin\Narekaltro\App\Url;

require_once("../../vendor/autoload.php");


$action = Url::getParam('action');

switch($action) {
    
    case "add":
    require_once('location/add.php');
    break;
    
    case "edit":
    require_once('location/edit.php');
    break;

    case "remove":
    require_once('location/remove.php');
    break;
    
    default:
    require_once('location/list.php');

}







