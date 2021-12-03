<?php 

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

$country_id = $_GET['country_id'];

if(isset($_GET['country_id'])) {
    $objLocation = new Location();
    echo json_encode($getStates = $objLocation->getStates($country_id));
}




