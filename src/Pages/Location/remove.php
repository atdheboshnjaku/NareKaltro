<?php

use Fin\Narekaltro\App\Session;
use Fin\Narekaltro\App\Login;
use Fin\Narekaltro\App\User;
use Fin\Narekaltro\App\Form;
use Fin\Narekaltro\App\Validation;

require_once("../../vendor/autoload.php");

// $objSession = new Session();
// if(!$objSession->isLogged()) {
//     Login::redirectTo("login");
// }

$objUser = new User();
$u =  $objUser->getUser($_SESSION['userId']);
echo $u['name'] . " ";
echo "<a href=\"/\">Home</a>\n";
echo "<a href=\"?action=add\">Add</a>\n";
echo "<a href=\"?action=edit\">Edit</a>\n";
echo "<a href=\"?action=delete\">Delete</a>\n";
echo "<a href=\"/users\">User</a>\n";
echo "<a href=\"locations\">Locations</a>\n";
echo "<a href=\"logout\">logout</a>\n";
echo "<br>";
echo "hi from delete";