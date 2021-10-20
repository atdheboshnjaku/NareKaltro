<?php

use Fin\Narekaltro\App\Session;
use Fin\Narekaltro\App\Login;
use Fin\Narekaltro\App\User;
use Fin\Narekaltro\App\Form;
use Fin\Narekaltro\App\Validation;
use Fin\Narekaltro\App\Url;

require_once("../../vendor/autoload.php");

$objSession = new Session();
if(!$objSession->isLogged()) {
    Login::redirectTo("login");
}



$objForm = new Form();
$objValidation = new Validation($objForm);
$objUser = new User();

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

oh noo










