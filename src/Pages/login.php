<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

use Fin\Narekaltro\App\Session;
use Fin\Narekaltro\App\Login;
use Fin\Narekaltro\App\Form;
use Fin\Narekaltro\App\User;
use Fin\Narekaltro\App\Validation;

require_once("../../vendor/autoload.php");

$objSession = new Session();
if ($objSession->isLogged()) {
    Login::redirectTo("/");
}

$objForm = new Form();
$objValidation = new Validation($objForm);

if ($objForm->isPost("email")) {

    $objUser = new User();
    //$check = isset($_POST['remember_me']) ? "checked" : false;
    if ($objUser->authenticate($objForm->getPost("email"), $objForm->getPost("password"), $objForm->isChecked("remember_me"))) {
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
            <h1>Welcome back@</h1>
            <form action="" method="post" class="login-form">
                <?php echo $objValidation->validate("login"); ?>
                <?php echo $objValidation->validate("email"); ?>
                <input type="email" name="email" placeholder="Email" required="">
                <?php echo $objValidation->validate("password"); ?>
                <input type="password" name="password" placeholder="Password" required="">
                <input type="checkbox" name="remember_me" id="remember_me" value="checked" />Remember Me
                <input type="submit" name="submit">

            </form>
            Don't have an account? <a href="/register">Sign-up</a>
        </div>

    </div>

</div>



<?php require_once("Templates/footer.php"); ?>