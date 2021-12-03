<?php

use Fin\Narekaltro\App\Session;
use Fin\Narekaltro\App\Login;
use Fin\Narekaltro\App\User;
use Fin\Narekaltro\App\Form;
use Fin\Narekaltro\App\Service;
use Fin\Narekaltro\App\Url;

require_once("../../../vendor/autoload.php");

$objSession = new Session();
if(!$objSession->isLogged()) {
    Login::redirectTo("/login");
}

$id = $_POST['id'];
if(!empty($id)) {
    $objService = new Service();

    if($objService->deleteService($id)) {
    Login::redirectTo("/services");
    } else {
        Login::redirectTo("/error");
    }

    
}









