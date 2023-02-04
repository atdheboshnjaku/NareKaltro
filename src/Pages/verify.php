<?php

use Fin\Narekaltro\App\Session;
use Fin\Narekaltro\App\Url;
use Fin\Narekaltro\App\Login;
use Fin\Narekaltro\App\Form;
use Fin\Narekaltro\App\User;
use Fin\Narekaltro\App\Validation;

require_once("../../vendor/autoload.php");

$objSession = new Session();
if($objSession->isLogged()) {
    Login::redirectTo("/");
}

$hash = Url::getParam('hash');

$objUser = new User();
if(!$objUser->hashExists($hash)) {
    Login::redirectTo("register");
}

if($objUser->hashVerified($hash)) {
    Login::redirectTo("login");
}

$objForm = new Form();
$objValidation = new Validation($objForm);

if($objForm->isPost("password")) {

    $objValidation->expected = [
        "name", 
        "password"
    ];

    $objValidation->required = ["name", "password"];

    $objValidation->postFormat = ["password" => "password"];

    if($objValidation->isValid()) {

        $objValidation->post['status'] = "1";
        if($objUser->verifyHash($hash)) {
            $user = $objUser->verifyHash($hash);
            if($objUser->updateUser($objValidation->post, $user['id'])) {
                $objUser->deleteHash($user['id']);
                if($objUser->authenticate($user['email'], $objForm->getPost("password"))) {
                    $objSession = new Session();
                    $objSession->login($objUser);
                    Login::redirectTo("/");
                } else {
                    $objValidation->addToErrors("login");
                }

            }
        }

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
                <input autocomplete="off" type="text" name="name" placeholder="Enter Your Fullname" required="required">
                <?php echo $objValidation->validate("password"); ?>
                <input autocomplete="off" type="password" name="password" placeholder="Strong Password" required="required">

                <input type="submit" name="submit" value="Continue">
                
            </form>

        </div>

    </div>

</div>



<?php require_once("Templates/footer.php"); ?>