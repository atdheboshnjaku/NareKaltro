<?php

use Fin\Narekaltro\App\Session;
use Fin\Narekaltro\App\Login;
use Fin\Narekaltro\App\Form;
use Fin\Narekaltro\App\User;
use Fin\Narekaltro\App\Validation;

require_once("../../vendor/autoload.php");

// $objSession = new Session();
// if($objSession->isLogged()) {
//     Login::redirectTo("/");
// }

$objForm = new Form();
$objValidation = new Validation($objForm);

if($objForm->isPost("email")) {
    
    $objUser = new User();

    $objValidation->expected = [
        "account_id",
        "role_id",
        "location_id",
        "email"
    ];

    $objValidation->required = [
        "email"
    ];

    $email = $objForm->getPost("email");
    $existingUser = $objUser->getUserByEmail($email);

    if(!empty($existingUser)) {
        $objValidation->addToErrors("user_exists");
    } 

    $objValidation->post['account_id'] = uniqid();
    $objValidation->post['role_id'] = "1";
    $objValidation->post['location_id'] = "1";
    $objValidation->post['country'] = "1";
    $objValidation->post['hash'] = uniqid();
    $objValidation->post['email'] = $_POST['email'];
    $objValidation->post['status'] = "0";
    //$objValidation->post['hash_date'] = date('YYYY-MM-DD hh:mm:ss');

    if($objUser->registerUser($objValidation->post)) {
        Login::redirectTo("login");
    } else {
        $objValidation->addToErrors("reg_failed");
    }

    // if($objUser->authenticate($objForm->getPost("email"), $objForm->getPost("password"))) {
    //     $objSession->login($objUser);
    //     Login::redirectTo("/");
    // } else {
    //     $objValidation->addToErrors("login");
    // }

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
            <h1>Create Free Account</h1>
            <form action="" method="post" class="login-form">
                <?php echo $objValidation->validate("user_exists"); ?>
                <?php echo $objValidation->validate("email"); ?>
                <?php echo $objValidation->validate("reg_failed"); ?>
                <input type="email" name="email" placeholder="Enter Your Email Address" required="">

                <input type="submit" name="submit" value="Sign Up">
                
            </form>

        </div>

    </div>

</div>



<?php require_once("Templates/footer.php"); ?>