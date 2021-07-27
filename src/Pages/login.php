<?php

use Fin\Narekaltro\App\Session;
use Fin\Narekaltro\App\Login;
use Fin\Narekaltro\App\Form;
use Fin\Narekaltro\App\User;

require_once("../../vendor/autoload.php");

$session = new Session();
if($session->isLogged()) {
    Login::redirectTo("/");
}

$form = new Form();

if($form->isPost("email")) {
    
    $user = new User();
    if($user->authenticate($form->getPost("email"), $form->getPost("password"))) {
        $session = new Session();
        $session->login($user);
        Login::redirectTo("/");
    } else {
        Login::redirectTo("error");
    }

} 


?>

<form action="" method="post">

    <p>
        <input type="email" name="email" placeholder="Email" required="">
    </p>
    <p>
        <input type="password" name="password" placeholder="Password" required="">
    </p>
    <p>
        <input type="submit" name="submit">
    </p>
    
</form>