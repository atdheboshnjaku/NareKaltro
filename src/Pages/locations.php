<?php phpinfo(); exit(); ?>
<?php

use Fin\Narekaltro\App\Url;

require_once("../../vendor/autoload.php");


$action = Url::getParam('action');

switch($action) {
    
    case "add":
    require_once('Location/add.php');
    break;
    
    case "edit":
    require_once('Location/edit.php');
    break;

    case "remove":
    require_once('Location/remove.php');
    break;
    
    default:
    require_once('Location/list.php');

}







