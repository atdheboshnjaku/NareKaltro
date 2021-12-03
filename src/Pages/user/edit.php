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
$objUser = new User();
$user = $objUser->getUser($id);
$userCount = $objUser->userCount();
$roles = $objUser->getUserRoles();
$objLocation = new Location();
$locations = $objLocation->getBusinessLocations();

if($objForm->isPost("name")) {

    $objValidation->expected = [
        "role_id", 
        "location_id", 
        "name", 
        "email", 
        "status",
    ];

    if($objForm->getPost("password")) {
        $objValidation->expected[] = "password";  
    }

    $objValidation->required = ["role_id", "location_id", "name", "email"];

    $objValidation->special = ["email" => "email"];

    if($objForm->getPost("password")) {
        $objValidation->postFormat = ["password" => "password"];
    }
    

    // $email = $objForm->getPost("email");
    // $existingUser = $objUser->getUserByEmail($email);

    // if(!empty($existingUser)) {
    //     $objValidation->addToErrors("user_exists");
    // }

    if($objValidation->isValid()) {
        if($objUser->updateUser($objValidation->post, $id)) {
            Login::redirectTo("/users");
        } else {
            Login::redirectTo("/error");
        }
    }
    
    // if($objValidation->isValid()) {
    //     if($objUser->updateUser($objValidation->post, $objForm->getPost("password"))) {
    //         Login::redirectTo("/users");
    //     } else {
    //         Login::redirectTo("/error");
    //     }
    // }

}

require_once("../Templates/header.php");

?>

<div class="box">
    <div class="box-header">
        <div class="box-lf-ctn">
            <h2>User</h2>
            <p>Edit user</p>
        </div>
        <div class="box-rt-ctn">
            <a href="/users"><button class="action-btn align-middle"><i class="fa fa-arrow-circle-o-left" aria-hidden="true"></i>&nbsp; Go Back</button></a>
        </div>
    </div>
    <form action="" method="post" class="add-form" enctype="multipart/form-data">
        <?php echo $objValidation->validate('role_id'); ?>
        <p>
            <select name="role_id">
                <option value="">Choose user role</option>
                <optgroup label="User role">
                    <?php foreach($roles as $role) { ?>
                        <option value="<?php echo $role['level']; ?>"
                        <?php echo $objForm->stickySelect('role_id', $role['level'], $user['role_id']); ?>>
                        <?php echo $role['name']; ?></option>
                    <?php } ?>
                </optgroup>
            </select>
        </p>
        <?php echo $objValidation->validate('location_id'); ?>
        <p>
            <select name="location_id">
                <option value="">Choose location</option>
                <optgroup label="User location">
                    <?php foreach($locations as $location) { ?>
                        <option value="<?php echo $location['id']; ?>"
                        <?php echo $objForm->stickySelect('location_id', $location['id'], $user['location_id']); ?>>
                        <?php echo $location['name']; ?></option>
                    <?php } ?>
                </optgroup>
            </select>
        </p>
        <?php echo $objValidation->validate('name'); ?>
        <p>
            <input type="hidden" name="status" value="1">
            <input type="text" name="name" value="<?php echo $objForm->stickyText('name', $user['name']); ?>" placeholder="Users full name" required="">
        </p>
        <?php echo $objValidation->validate('email'); ?>
        <p>
            <input type="email" name="email" value="<?php echo $objForm->stickyText('email', $user['email']); ?>" placeholder="User email" required="">
        </p>
        <p>
            <input type="password" name="password" placeholder="Password" >
        </p>
        <p>
            <input type="submit" name="submit" class="blue-btn alab" value="Update user">
        </p>

    </form>
</div>

<?php require_once("../Templates/footer.php"); ?>


