<?php

use Fin\Narekaltro\App\Session;
use Fin\Narekaltro\App\Login;
use Fin\Narekaltro\App\User;
use Fin\Narekaltro\App\Form;
use Fin\Narekaltro\App\Validation;

require_once("../../vendor/autoload.php");

$objSession = new Session();
if(!$objSession->isLogged()) {
    Login::redirectTo("login");
}

$objForm = new Form();
$objValidation = new Validation($objForm);
$objUser = new User();
$userCount = $objUser->userCount();

if($objForm->isPost("name")) {

    $objValidation->expected = ["role_id", "location_id", "name", "email", "password"];
    $objValidation->required = ["name", "email", "password"];

    $objValidation->special = ["email" => "email"];
    $objValidation->postFormat = ["password" => "password"];

    $email = $objForm->getPost("email");
    $existingUser = $objUser->getUserByEmail($email);

    if(!empty($existingUser)) {
        $objValidation->addToErrors("user_exists");
    } 

    // $email = $objForm->getPost('email');
    // $user = $objUser->getByEmail($email);
    
    // if (!empty($user)) {
    //     $objValid->add2Errors('email_duplicate');
    // }

    if($objValidation->isValid()) {
        if($objUser->createUser($objValidation->post, $objForm->getPost("password"))) {
            Login::redirectTo("users");
        } else {
            Login::redirectTo("error");
        }
    } 

}

require_once("Templates/header.php");

?>

<div class="box">
    <div class="box-header">
        <div class="box-lf-ctn">
            <h2>Users</h2>
            <p><?php echo array_shift($userCount); ?> users in total</p>
        </div>
        <div class="box-rt-ctn">
            
        </div>
    </div>
    <table class="action-table align-middle">
        <form action="" method="post">
            
            <p>
                <input type="hidden" name="role_id" value="1">
            </p>
            <p>
                <input type="hidden" name="location_id" value="1">
            </p>
            <p>
                <input type="text" name="name" placeholder="User name" required="">
            </p>
            <?php echo $objValidation->validate('user_exists'); ?>
            <p>
                <input type="email" name="email" placeholder="User email" required="">
            </p>
            <p>
                <input type="password" name="password" placeholder="Password" required="">
            </p>
            <!-- <p>
                <select>
                    <option>Choose location</option>
                    <optgroup label="User location">
                        <option>NareKaltro Gjakove</option>
                        <option>NareKaltro Prishtine</option>
                    </optgroup>
                </select>
            </p>
            <p>
                <select>
                    <option>Choose user role</option>
                    <optgroup label="User role">
                        <option>Admin</option>
                        <option>Employee</option>
                    </optgroup>
                </select>
            </p> -->
            <p>
                <input type="submit" name="" value="Add user">
            </p>

        </form>
    </table>
</div>