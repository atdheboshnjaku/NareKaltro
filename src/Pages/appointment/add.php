<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

use Fin\Narekaltro\App\Session;
use Fin\Narekaltro\App\Login;
use Fin\Narekaltro\App\User;
use Fin\Narekaltro\App\Appointments;

require_once("../../../vendor/autoload.php");

$objSession = new Session();
if(!$objSession->isLogged()) {
    Login::redirectTo("/login");
}

$objUser = new User();
$userId = $objSession->getUserId();
$userAccount = $objUser->getUserAccountID($userId);

$location = $_POST['location_id'];
$client = $_POST['client_id'];

$services = $_POST['implodedArray'];
$notes = $_POST['a_appointment_notes'];
$appStart = $_POST['start_date'];
if(!empty($_POST['end_date'])) {

    $appEnd = $_POST['end_date'];
    $objAppointment = new Appointments();
    $params = [
        'account_id' => $userAccount,
        'location_id' => $location,
        'client_id' => $client,
        'service_id' => $services,
        'start_date' => $appStart,
        'end_date' => $appEnd,
        'appointment_notes' => $notes,
    ];
    $objAppointment->addAppointment($params);
} else {

    $objAppointment = new Appointments();
    $params = [
        'account_id' => $userAccount,
        'location_id' => $location,
        'client_id' => $client,
        'service_id' => $services,
        'start_date' => $appStart,
        'appointment_notes' => $notes,
    ];
    $objAppointment->addAppointment($params);
}

$appointmentId = $objAppointment->getCreatedAppointmentId();
$objAppointment->saveServiceCosts($appointmentId, $_POST['service_cost']);