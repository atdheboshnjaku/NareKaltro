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

$objUser = new User();
$userCount = $objUser->userCount();

require_once("Templates/header.php");
?>

<div class="box">
    <div class="box-header">
        <div class="box-lf-ctn">
            <h2>Users</h2>
            <p><?php echo array_shift($userCount); ?> users in total</p>
        </div>
        <div class="box-rt-ctn">
            <a href="/users/add"><button class="action-btn align-middle"><i class="fa fa-plus-square-o" aria-hidden="true"></i>&nbsp; New User</button></a>
        </div>
    </div>
    <table class="action-table align-middle">
        <thead>
            <tr>
                <th>Name</th>
                <th>Email</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php $allUsers = $objUser->getUsers(); ?>
            <?php foreach($allUsers as $aUser) { ?>
                <tr>
                    <td>
                        <?php echo $aUser['name']; ?>
                        <p>Gjakove</p>
                    </td>
                    <td>
                        <?php echo $aUser['email']; ?><br>
                        <p class="badge badge-vacation">Vacation</p>
                    </td> 
                    <td>
                        <a href="/users/edit/<?php echo $aUser['id']; ?>">
                            <div class="btn btn-icon"><i class="fa fa-pencil-square-o" aria-hidden="true"></i></div>
                        </a> 
                        <a href="&quest;action=remove&amp;id=<?php echo $aUser['id']; ?>">
                            <div class="btn btn-icon"><i class="fa fa-trash-o" aria-hidden="true"></i></div>
                        </a>
                    </td>
                </tr>
            <?php } ?>            
        </tbody>
    </table>
</div>

<?php require_once("Templates/footer.php"); ?>








