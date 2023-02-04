<?php

use Fin\Narekaltro\App\Session;
use Fin\Narekaltro\App\Login;
use Fin\Narekaltro\App\User;
use Fin\Narekaltro\App\Form;
use Fin\Narekaltro\App\Validation;
use Fin\Narekaltro\App\Service;

require_once("../../../vendor/autoload.php");

$objForm = new Form();
$objValidation = new Validation($objForm);
$objService = new Service();

$objSession = new Session();
if(!$objSession->isLogged()) {
    Login::redirectTo("/login");
}

if($objForm->isPost("name")) {

    $objValidation->expected = ["name", "background", "color", "status"];
    $objValidation->required = ["name"];

    $service = $objForm->getPost("name");
    $existingService = $objService->getServiceByName($service);

    if(!empty($existingService)) {
        $objValidation->addToErrors("service_exists");
    } 

    $objUser = new User();
    $userAccount = $objUser->getUserAccountID($objSession->getUserId());
   

    if($objValidation->isValid()) {
        $objValidation->post['account_id'] = $userAccount;
        if($objService->createService($objValidation->post)) {
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
            <input type="text" name="name" placeholder="Service name" required="">
        </p>
        <p>
            <span>Service background color</span>
            <input type="color" name="background">
        </p>
        <p>
            <span>Service text color</span>
           <input type="color" name="color"> 
        </p>
        <p>
            <input type="submit" name="submit" class="blue-btn alab" value="Add service">
        </p>
        <input type="hidden" name="status" value="1">
    </form>
</div>

<?php require_once("../Templates/footer.php"); ?>