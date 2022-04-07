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

$state_id = $_GET['state_id'];
$country_id = $_GET['country_id'];

if(isset($_GET['state_id']) && isset($_GET['country_id'])) {
    $objLocation = new Location();
    echo json_encode($getCities = $objLocation->getCities($state_id, $country_id));
}


 

