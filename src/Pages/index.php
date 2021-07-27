<?php 

use Fin\Narekaltro\App\Database;
use Fin\Narekaltro\App\Session;
use Fin\Narekaltro\App\Login;
use Fin\Narekaltro\App\User;

require_once("../../vendor/autoload.php");

// $session = new Session();
// if(!$session->isLogged()) {
//     Login::redirectTo("login");
// }

$user = new User();
$u =  $user->getUser(1);
echo $u['name'] . " ";
echo "<a href=\"logout\">logout</a>";


//echo "you are logged in!";