<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

use Fin\Narekaltro\App\Session;
use Fin\Narekaltro\App\Login;
use Fin\Narekaltro\App\Appointments;
use Fin\Narekaltro\App\User;

require_once("../../../vendor/autoload.php");

$objSession = new Session();
if (!$objSession->isLogged()) {
    Login::redirectTo("/login");
}

$location = (isset($_POST['e_location_id'])) ? $_POST['e_location_id'] : "NULL";
$service = $_POST['implodedServicesArray'];
$notes = (isset($_POST['appointment_notes'])) ? $_POST['appointment_notes'] : "NULL";
$start_date = (isset($_POST['e_start_date'])) ? $_POST['e_start_date'] : "NULL";
$end_date = (isset($_POST['e_end_date'])) ? $_POST['e_end_date'] : "1970-01-01 08:00:00";

$objUser = new User();
$userId = $objSession->getUserId();
$userAccount = $objUser->getUserAccountID($userId);

$appointment_id = (isset($_POST['id'])) ? $_POST['id'] : "NULL";

if (!empty($start_date) && !empty($appointment_id)) {

    if (!empty($_POST['e_end_date'])) {

        $params = [
            'location_id' => $location,
            'service_id' => $service,
            'start_date' => $start_date,
            'end_date' => $end_date,
            'appointment_notes' => $notes
        ];
        $objAppointment = new Appointments();
        $objAppointment->updateAppointmentDate($params, $appointment_id);
    } else {

        $params = [
            'location_id' => $location,
            'service_id' => $service,
            'start_date' => $start_date,
            'appointment_notes' => $notes
        ];
        $objAppointment = new Appointments();
        $objAppointment->updateAppointmentDate($params, $appointment_id);
    }

    $objAppointment->saveServiceCosts($appointment_id, $_POST['service_cost']);
}