<?php

use Fin\Narekaltro\App\Url;

require_once("../../vendor/autoload.php");


$action = Url::getParam('action');

switch($action) {
    
    case "add":
    require_once('user/add.php');
    break;
    
    case "edit":
    require_once('user/edit.php');
    break;

    case "remove":
    require_once('user/remove.php');
    break;
    
    default:
    require_once('user/list.php');

}







