<?php

use Fin\Narekaltro\App\Session;
use Fin\Narekaltro\App\Login;
use Fin\Narekaltro\App\User;
use Fin\Narekaltro\App\Form;
use Fin\Narekaltro\App\Location;
use Fin\Narekaltro\App\Url;

require_once("../../../vendor/autoload.php");

$objSession = new Session();
if(!$objSession->isLogged()) {
    Login::redirectTo("/login");
}

$id = $_POST['id'];
if(!empty($id)) {
    $objLocation = new Location();
    $objUser = new User();
    if(!$objUser->checkUserHasThisLocation($id)) {
        if($objLocation->deleteLocation($id)) {
        Login::redirectTo("/locations");
        } else {
            Login::redirectTo("/error");
        }
    }
    
}



//require_once("../Templates/header.php");









