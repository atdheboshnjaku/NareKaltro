<?php 

use Fin\Narekaltro\App\Database;
use Fin\Narekaltro\App\Session;
use Fin\Narekaltro\App\Login;
use Fin\Narekaltro\App\User;
use Fin\Narekaltro\App\Appointments;

require_once("../../vendor/autoload.php");

$session = new Session();
if(!$session->isLogged()) {
    Login::redirectTo("/login");
}


require_once("Templates/header.php");
?>


    
home



<?php require_once("Templates/footer.php"); ?>

