<?php 

use Fin\Narekaltro\App\Database;
use Fin\Narekaltro\App\Session;
use Fin\Narekaltro\App\Login;
use Fin\Narekaltro\App\User;
use Fin\Narekaltro\App\Appointments;

require_once("../../../vendor/autoload.php");

$objSession = new Session();
if(!$objSession->isLogged()) {
    Login::redirectTo("/login");
}

$objAppointments = new Appointments();

echo $appointments = $objAppointments->getAppointmentsJSON();






