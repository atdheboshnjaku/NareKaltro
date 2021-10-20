<?php

use Fin\Narekaltro\App\Url;

require_once("../../vendor/autoload.php");


$action = Url::getParam('action');

switch($action) {
    
    case "add":
    require_once('User/add.php');
    break;
    
    case "edit":
    require_once('User/edit.php');
    break;

    case "remove":
    require_once('User/remove.php');
    break;
    
    default:
    require_once('User/list.php');

}







