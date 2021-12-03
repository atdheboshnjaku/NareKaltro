<?php

use Fin\Narekaltro\App\Session;
use Fin\Narekaltro\App\Login;
use Fin\Narekaltro\App\User;
use Fin\Narekaltro\App\Form;
use Fin\Narekaltro\App\Validation;
use Fin\Narekaltro\App\Service;
use Fin\Narekaltro\App\Url;

require_once("../../../vendor/autoload.php");

$id = Url::getParam("id");
$objForm = new Form();
$objValidation = new Validation($objForm);
$objService = new Service();
$service = $objService->getServiceById($id);

$objSession = new Session();
if(!$objSession->isLogged()) {
    Login::redirectTo("/login");
}

if($objForm->isPost("name")) {

    $objValidation->expected = ["name", "background", "color", "status"];
    $objValidation->required = ["name"];

    $service = $objForm->getPost("name");
    $existingService = $objService->getServiceByName($service, $id);

    if(!empty($existingService)) {
        $objValidation->addToErrors("service_exists");
    } 

    if($objValidation->isValid()) {
        if($objService->updateService($objValidation->post, $id)) {
            Login::redirectTo("/services");
        } else {
            Login::redirectTo("/error");
        }
    } 

}

require_once("../Templates/header.php");

?>

<div class="box">
    <div class="box-header">
        <div class="box-lf-ctn">
            <h2>Services</h2>
            <p>Add your new service</p>
        </div>
        <div class="box-rt-ctn">
            <a href="/services"><button class="action-btn align-middle"><i class="fa fa-arrow-circle-o-left" aria-hidden="true"></i>&nbsp; Go Back</button></a>
        </div>
    </div>
    <form action="" method="post" class="add-form">
        
        <?php echo $objValidation->validate('service_exists'); ?>
        <p>
            <span>Service name</span>
            <input type="text" name="name" value="<?php echo $objForm->stickyText('name', $service['name']); ?>" placeholder="Service name" required="">
        </p>
        <p>
            <span>Service background color</span>
            <input type="color" name="background" value="<?php echo $objForm->stickyText('background', $service['background']); ?>">
        </p>
        <p>
            <span>Service text color</span>
           <input type="color" name="color" value="<?php echo $objForm->stickyText('color', $service['color']); ?>"> 
        </p>
        <p>
            <input type="submit" name="submit" class="blue-btn alab" value="Update service">
        </p>
        <input type="hidden" name="status" value="1">
    </form>
</div>

<?php require_once("../Templates/footer.php"); ?>