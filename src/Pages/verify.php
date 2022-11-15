<?php

use Fin\Narekaltro\App\Url;
use Fin\Narekaltro\App\Login;
use Fin\Narekaltro\App\Form;
use Fin\Narekaltro\App\User;
use Fin\Narekaltro\App\Validation;

require_once("../../vendor/autoload.php");

// $objSession = new Session();
// if($objSession->isLogged()) {
//     Login::redirectTo("/");
// }

$hash = Url::getParam('hash');

$objForm = new Form();
$objValidation = new Validation($objForm);

if($objForm->isPost("password")) {
    
    $objUser = new User();
    if($objUser->verifyUser($objForm->getPost("name"), $hash, $objForm->getPost("password"))) {
        $objSession->login($objUser);
        Login::redirectTo("/");
    } else {
        $objValidation->addToErrors("login");
    }

} 

require_once("Templates/header.php");
?>

<div class="login-ctn">

    <div class="login-intro-img">
        <img src="Resources/img/1.svg">
        <!-- <img src="Resources/img/appointment_wallpaper.svg"> -->
    </div>

    <div class="login-form-ctn">

        <div class="form-ctn">
            <h1>Verify</h1>
            <form action="" method="post" class="login-form">
                <?php echo $objValidation->validate("name"); ?>
                <input type="text" name="name" placeholder="Enter Your Fullname" required="">
                <?php echo $objValidation->validate("password"); ?>
                <input type="password" name="password" placeholder="Strong Password" required="">

                <input type="submit" name="submit" value="Continue">
                
            </form>

        </div>

    </div>

</div>



<?php require_once("Templates/footer.php"); ?>