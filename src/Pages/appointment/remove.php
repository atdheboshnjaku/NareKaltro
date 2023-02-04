<?php

use Fin\Narekaltro\App\Appointments;
use Fin\Narekaltro\App\Session;
use Fin\Narekaltro\App\Login;

require_once("../../../vendor/autoload.php");

$objSession = new Session();
if(!$objSession->isLogged()) {
    Login::redirectTo("/login");
}

$id = $_POST['id'];

if(!empty($id)) {

    $objAppointment = new Appointments();
    $objAppointment->cancelAppointment($id);

}


