<?php

use Fin\Narekaltro\App\Session;
use Fin\Narekaltro\App\Login;
use Fin\Narekaltro\App\User;
use Fin\Narekaltro\App\Form;
use Fin\Narekaltro\App\Validation;
use Fin\Narekaltro\App\Location;

require_once("../../vendor/autoload.php");

$objForm = new Form();
$objValidation = new Validation($objForm);
$objLocation = new Location();

$objSession = new Session();
if(!$objSession->isLogged()) {
    Login::redirectTo("login");
}

if($objForm->isPost("name")) {

    $objValidation->expected = ["name"];
    $objValidation->required = ["name"];

    $location = $objForm->getPost("name");
    $existingLocation = $objLocation->getLocationByEmail($location);

    if(!empty($existingLocation)) {
        $objValidation->addToErrors("location_exists");
    } 

    if($objValidation->isValid()) {
        if($objLocation->createLocation($objValidation->post)) {
            Login::redirectTo("locations");
        } else {
            Login::redirectTo("error");
        }
    } 

}

require_once("Templates/header.php");

?>

<form action="" method="post">
    <?php echo $objValidation->validate('location_exists'); ?>
    <p>
        <input type="text" name="name" placeholder="Location name" required="">
    </p>
    <p>
        <input type="submit" name="" value="Add location">
    </p>
</form>