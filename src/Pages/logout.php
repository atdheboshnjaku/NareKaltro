<?php
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

use Fin\Narekaltro\App\Session;
use Fin\Narekaltro\App\Login;
use Fin\Narekaltro\App\User;

require_once("../../vendor/autoload.php");

$session = new Session();
$user = new User();
if(isset($_SESSION['userId'])) {

    $user->deleteUserToken($_SESSION['userId']);

}
$session->logout();
Login::redirectTo("/login");

