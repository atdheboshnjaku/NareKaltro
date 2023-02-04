<?php 

use Fin\Narekaltro\App\Database;
use Fin\Narekaltro\App\Session;
use Fin\Narekaltro\App\Login;
use Fin\Narekaltro\App\User;
use Fin\Narekaltro\App\Appointments;

// $currentPage = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";

// if ($_SERVER['REQUEST_METHOD'] == "GET" && strcmp(basename($currentPage), basename(__FILE__)) == 0)
// {
//     http_response_code(404);
//     //include('myCustom404.php'); // provide your own 404 error page
//     die(); /* remove this if you want to execute the rest of
//               the code inside the file before redirecting. */
// }

require_once("../../../vendor/autoload.php");

$objSession = new Session();
if(!$objSession->isLogged()) {
    Login::redirectTo("/login");
}

$objAppointments = new Appointments();
$objUser = new User();    
$userId = $objSession->getUserId();
$userAccount = $objUser->getUserAccountID($userId);

echo $appointments = $objAppointments->getAppointmentsJSON($userAccount);






