<?php

use Fin\Narekaltro\App\Session;
use Fin\Narekaltro\App\Login;
use Fin\Narekaltro\App\User;
use Fin\Narekaltro\App\Url;

require_once("../../../vendor/autoload.php");

$objSession = new Session();
if(!$objSession->isLogged()) {
    Login::redirectTo("/login");
}

$id = $_POST['id'];
if(!empty($id)) {
    $objUser = new User();
    if($objUser->removeUser($id)) {
        Login::redirectTo("/users");
    } else {
        Login::redirectTo("/error");
    }
    
}

//require_once("../Templates/header.php");









