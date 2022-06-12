<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
 
use Fin\Narekaltro\App\Session;
use Fin\Narekaltro\App\Login;
use Fin\Narekaltro\App\User;
use Fin\Narekaltro\App\Form;
use Fin\Narekaltro\App\Validation;
use Fin\Narekaltro\App\Location;

require_once("../../../vendor/autoload.php");

$objSession = new Session();
if(!$objSession->isLogged()) {
    Login::redirectTo("/login");
}

$c_role_id = $_POST['c_role_id'];
$c_location_id = $_POST['c_location_id'];
$c_name = $_POST['c_name'];
$c_email = $_POST['c_email'];
$c_number = $_POST['c_number'];
$country = $_POST['country'];
$state = $_POST['state'];
$city = (isset($_POST['city'])) ? $_POST['city'] : "NULL";
$c_status = $_POST['c_status'];


if(!empty($c_location_id) && !empty($c_name)) {

    $objUser = new User();
    $params = [

        'role_id' => $c_role_id,
        'location_id' => $c_location_id,
        'name' => $c_name,
        'email' => $c_email,
        'number' => $c_number,
        'country' => $country,
        'state' => $state,
        'city' => $city,
        'status' => $c_status      
    ];

    //$objUser->createUser($params);

    if($objUser->createUser($params)) {
        echo $objUser->getCreatedUserID();
    }

}






