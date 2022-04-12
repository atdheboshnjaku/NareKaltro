<?php 
ini_set('display_errors', 'On');

use Fin\Narekaltro\App\Database;
use Fin\Narekaltro\App\Session;
use Fin\Narekaltro\App\Login;
use Fin\Narekaltro\App\User;
use Fin\Narekaltro\App\Appointments;
use Fin\Narekaltro\App\Location;

require_once("../../../vendor/autoload.php");

$objSession = new Session();
if(!$objSession->isLogged()) {
    Login::redirectTo("/login");
}

$start_date = (isset($_POST['start_date'])) ? $_POST['start_date'] : "NULL";
$end_date = (isset($_POST['end_date'])) ? $_POST['end_date'] : "NULL";
$appointment_id = (isset($_POST['appointment_id'])) ? $_POST['appointment_id'] : "NULL";

if(!empty($start_date) && !empty($appointment_id)) {

    $objAppointment = new Appointments();
    $objAppointment->updateAppointmentDate($appointment_id, $start_date, $end_date);

}



