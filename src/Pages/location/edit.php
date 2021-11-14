<?php

use Fin\Narekaltro\App\Session;
use Fin\Narekaltro\App\Login;
use Fin\Narekaltro\App\User;
use Fin\Narekaltro\App\Form;
use Fin\Narekaltro\App\Validation;
use Fin\Narekaltro\App\Url;
use Fin\Narekaltro\App\Location;

require_once("../../../vendor/autoload.php");

$objSession = new Session();
if(!$objSession->isLogged()) {
    Login::redirectTo("/login");
}

$id = Url::getParam("id");
$objForm = new Form();
$objValidation = new Validation($objForm);
$objLocation = new Location();
$location = $objLocation->getLocationById($id);

if($objForm->isPost("name")) {

    $objValidation->expected = ["name"];
    $objValidation->required = ["name"];

    $location = $objForm->getPost("name");
    $existingLocation = $objLocation->getLocationByName($location);

    if(!empty($existingLocation)) {
        $objValidation->addToErrors("location_exists");
    } 

    if($objValidation->isValid()) {
        if($objLocation->updateLocation($objValidation->post, $id)) {
            Login::redirectTo("/locations");
        } else {
            Login::redirectTo("/location/edit?id={$id}");
        }
    } 

}

require_once("../Templates/header.php");

?>

<div class="box">
    <div class="box-header">
        <div class="box-lf-ctn">
            <h2>Location</h2>
            <p>Edit your location</p>
        </div>
        <div class="box-rt-ctn">
            <a href="/locations"><button class="action-btn align-middle"><i class="fa fa-arrow-circle-o-left" aria-hidden="true"></i>&nbsp; Go Back</button></a>
        </div>
    </div>
    <form action="" method="post" class="add-form">
        
        <?php echo $objValidation->validate('location_exists'); ?>
        <p>
            <input type="text" name="name" value="<?php echo $location['name']; ?>" placeholder="Location name" required="">
        </p>
        <p>
            <input type="submit" name="submit" class="blue-btn alab" value="Update location">
        </p>

    </form>
</div>

<?php require_once("../Templates/footer.php"); ?>



