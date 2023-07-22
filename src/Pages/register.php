<?php

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
require_once("Templates/header.php");

if ($objForm->isPost("email")) {

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

    $objUser = new User();
    $existingUser = $objUser->getUserByEmail($email);

    if (!empty($existingUser)) {
        $objValidation->addToErrors("user_exists");
    }

    if ($objValidation->isValid()) {

        $objValidation->post['account_id'] = uniqid('', true);
        $objValidation->post['role_id'] = "2";
        $objValidation->post['location_id'] = "0";
        $objValidation->post['date'] = date('Y-m-d H:i:s');
        //$objValidation->post['date'] = "NOW()";
        $objValidation->post['country'] = "0";
        $hash = $objUser->generateHash();
        $objValidation->post['hash'] = $hash;
        $objValidation->post['email'] = $_POST['email'];
        $objValidation->post['status'] = "0";

        if ($objUser->registerUser($objValidation->post)) { ?>
            <script type="text/javascript">

                $(document).ready(function () {

                    swal({
                        title: "Thank you for joining us!",
                        text: "We have sent a verification email to the email address you provided, please also check your spam/junk box and click on Verify",
                        icon: "success",
                        showConfirmButton: true,
                        //timer: 5000,
                    })
                });

            </script>
            <?php
            //Login::redirectTo("login");
        } else {
            $objValidation->addToErrors("reg_failed");
        }

    }


}

//require_once("Templates/header.php");
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
            Already have an account? <a href="/login">Login</a>
        </div>

    </div>

</div>

<script type="text/javascript">


    $(document).ready(function () {

        function registrationComplete() {

            e.preventDefault();

            swal({
                title: "Thank you joining us!",
                text: "We have sent a verification email to the email address you entered, please also check your spam/junk box and click on Verify",
                icon: "success",
                timer: 5000,
            });

        };

    });

</script>

<?php require_once("Templates/footer.php"); ?>