<?php

use Fin\Narekaltro\App\Session;
use Fin\Narekaltro\App\Login;
use Fin\Narekaltro\App\User;
use Fin\Narekaltro\App\Form;
use Fin\Narekaltro\App\Validation;
use Fin\Narekaltro\App\Url;
use Fin\Narekaltro\App\Location;

require_once("../../vendor/autoload.php");

$objSession = new Session();
if(!$objSession->isLogged()) {
    Login::redirectTo("/login");
}

$objUser = new User();
$userAccount = $objUser->getUserAccountID($objSession->getUserId());
$userCount = $objUser->userCount($userAccount, $objSession->getUserId());
$objLocation = new Location();


require_once("Templates/header.php");
?>

<div class="box">
    <div class="box-header">
        <div class="box-lf-ctn">
            <h2>Users</h2>
            <p><?php echo array_shift($userCount); ?> users in total</p>
        </div>
        <div class="box-rt-ctn">
            <a href="/user/add"><button class="action-btn align-middle"><i class="fa fa-plus-square-o" aria-hidden="true"></i>&nbsp; New User</button></a>
        </div>
    </div>
    <table class="action-table align-middle">
        <thead>
            <tr>
                <th>Name</th>
                <th>Email</th>
                <th>Level</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php $users = $objUser->getUsers($objSession->getUserId(), $userAccount); ?>
            <?php foreach($users as $user) { ?>
                <tr>
                    <td>
                        <?php echo $user['name']; ?>
                        <p>
                            <?php 
                                $location = $objLocation->getLocationById($user['location_id']); 
                                if($location) { echo $location['name']; }
                            ?>    
                        </p>
                    </td>
                    <td>
                        <?php echo $user['email']; ?><br>
                        <p class="badge badge-vacation">Vacation</p>
                    </td> 
                    <td>
                        <?php 
                            $level = $objUser->getUserLevelName($user['role_id']);
                            echo $level['name'];
                        ?>
                    </td>
                    <td>
                        <input type="hidden" class="delete-id" value="<?php echo $user['id']; ?>" >
                        <a href="/user/edit?id=<?php echo $user['id']; ?>">
                            <div class="btn btn-icon"><i class="fa fa-pencil-square-o" aria-hidden="true"></i></div>
                        </a> 
                        <a class="delete-confirmation">
                            <div class="btn btn-icon"><i class="fa fa-trash-o" aria-hidden="true"></i></div>
                        </a>
                    </td>
                </tr>
            <?php } ?>            
        </tbody>
    </table>
</div>

<script type="text/javascript">
    
$(document).ready(function(){

    $('.delete-denied').click(function(e) {

        e.preventDefault();

        swal({
            title: "Unable to Delete!",
            text: "You cannot delete a location that has users assigned to it",
            icon: "warning",
            timer: 5000,
        });

    });

    $('.delete-confirmation').click(function(e) {
        e.preventDefault();

        var deleteID = $(this).closest("tr").find('.delete-id').val();

        swal({
            title: "Remove User?",
            text: "Once removed, this user will no longer be available!",
            icon: "warning",
            buttons: true,
            dangerMode: true,
        })
        .then((willDelete) => {
            if (willDelete) {
            
                $.ajax({
                    type: "POST",
                    url: "/user/remove",
                    data: {
                        "id": deleteID,
                    },
                    success: function (response) {
                        
                        swal("User Removed Successfully!", {
                            icon: "success",
                        }).then((result) => {
                            location.reload();
                        });

                    }
                });

            } 
        });

    });

});

</script>

<?php require_once("Templates/footer.php"); ?>








