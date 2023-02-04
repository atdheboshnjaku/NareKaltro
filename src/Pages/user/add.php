<?php

use Fin\Narekaltro\App\Session;
use Fin\Narekaltro\App\Login;
use Fin\Narekaltro\App\User;
use Fin\Narekaltro\App\Form;
use Fin\Narekaltro\App\Validation;
use Fin\Narekaltro\App\Location;

require_once("../../../vendor/autoload.php");

$objSession = new Session();
if(!$objSession->isLogged()) {
    Login::redirectTo("/login");
}

$objForm = new Form();
$objValidation = new Validation($objForm);
$objUser = new User();
$userAccount = $objUser->getUserAccountID($objSession->getUserId());
//$userCount = $objUser->userCount();
$roles = $objUser->getUserRoles();
$objLocation = new Location();
$locations = $objLocation->getBusinessLocations($userAccount);

if($objForm->isPost("name")) {

    $objValidation->expected = ["role_id", "account_id", "location_id", "name", "email", "password", "status"];
    $objValidation->required = ["name", "email", "password"];

    $objValidation->special = ["email" => "email"];
    $objValidation->postFormat = ["password" => "password"];

    $email = $objForm->getPost("email");
    $existingUser = $objUser->getUserByEmail($email);

    if(!empty($existingUser)) {
        $objValidation->addToErrors("user_exists");
    } 

    if($objValidation->isValid()) {
        $objValidation->post['account_id'] = $userAccount;
        if($objUser->createUser($objValidation->post, $objForm->getPost("password"))) {
            Login::redirectTo("/users");
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
            <h2>Users</h2>
            <p>Add your new user</p>
        </div>
        <div class="box-rt-ctn">
            <a href="/users"><button class="action-btn align-middle"><i class="fa fa-arrow-circle-o-left" aria-hidden="true"></i>&nbsp; Go Back</button></a>
        </div>
    </div>
    <form action="" method="post" class="add-form">
        
        <p>
            <select name="role_id">
                <option>Choose user role</option>
                <optgroup label="User role">
                    <?php foreach($roles as $role) { ?>
                        <option value="<?php echo $role['level']; ?>"><?php echo $role['name']; ?></option>
                    <?php } ?>
                </optgroup>
            </select>
        </p>
        <p>
            <select name="location_id">
                <option>Choose location</option>
                <optgroup label="User location">
                    <?php foreach($locations as $location) { ?>
                        <option value="<?php echo $location['id']; ?>"><?php echo $location['name']; ?></option>
                    <?php } ?>
                </optgroup>
            </select>
        </p>
        <p>
            <input type="hidden" name="status" value="1">
            <input type="text" name="name" placeholder="Users full name" required="">
        </p>
        <?php echo $objValidation->validate('user_exists'); ?>
        <?php echo $objValidation->validate('email'); ?>
        <p>
            <input type="email" name="email" placeholder="User email" required="">
        </p>
        <p>
            <input type="password" name="password" placeholder="Password" required="">
        </p>
        <p>
            <input type="submit" name="submit" class="blue-btn alab" value="Add user">
        </p>

    </form>
</div>

<?php require_once("../Templates/footer.php"); ?>