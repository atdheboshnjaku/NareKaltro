<?php 

use Fin\Narekaltro\App\Session;
use Fin\Narekaltro\App\Login;
use Fin\Narekaltro\App\Appointments;

require_once("../../../vendor/autoload.php");

$objSession = new Session();
if(!$objSession->isLogged()) {
    Login::redirectTo("/login");
}

$start_date = (isset($_POST['start_date'])) ? $_POST['start_date'] : "NULL";
$end_date = (isset($_POST['end_date'])) ? $_POST['end_date'] : "1970-01-01 08:00:00";
$appointment_id = (isset($_POST['appointment_id'])) ? $_POST['appointment_id'] : "NULL";

if(!empty($start_date) && !empty($appointment_id)) {

    $params = [
        'start_date' => $start_date,
        'end_date' => $end_date
    ];
    $objAppointment = new Appointments();
    $objAppointment->updateAppointmentDate($params, $appointment_id);

}


