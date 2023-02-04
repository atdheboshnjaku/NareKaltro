<?php 
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

use Fin\Narekaltro\App\Database;
use Fin\Narekaltro\App\Session;
use Fin\Narekaltro\App\Login;
use Fin\Narekaltro\App\User;
use Fin\Narekaltro\App\Appointments;

require_once("../../vendor/autoload.php");

$objSession = new Session();
if(!$objSession->isLogged()) {
    Login::redirectTo("/login");
}


require_once("Templates/header.php");
?>

<div class="box">
    
<?php 

echo "ID: ".$_SESSION['userId'];
echo "<br><hr><br>";
echo "Name: ".$_SESSION['username'];

?>
</div>

<?php require_once("Templates/footer.php"); ?>

