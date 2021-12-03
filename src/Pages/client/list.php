<?php

use Fin\Narekaltro\App\Session;
use Fin\Narekaltro\App\Login;
use Fin\Narekaltro\App\User;
use Fin\Narekaltro\App\Form;
use Fin\Narekaltro\App\Validation;
use Fin\Narekaltro\App\Location;

require_once("../../vendor/autoload.php");

$objSession = new Session();
if(!$objSession->isLogged()) {
    Login::redirectTo("/login");
}

$objForm = new Form();
$objValidation = new Validation($objForm);
$objLocation = new Location();

$objUser = new User();
$clientCount = $objUser->clientCount();

require_once("Templates/header.php");

?>



<div class="box">
    <div class="box-header">
        <div class="box-lf-ctn">
            <h2>Clients</h2>
            <p><?php echo array_shift($clientCount); ?> clients in total</p>
        </div>
        <div class="box-rt-ctn">
            <a href="/client/add"><button class="action-btn align-middle"><i class="fa fa-plus-square-o" aria-hidden="true"></i>&nbsp; New Client</button></a>
        </div>
    </div>
    <table class="action-table center-title align-middle">
        <thead>
            <tr>
                <th><i class="fa fa-user-circle-o fa-lg" aria-hidden="true"></i></th>
                <th>Client</th>
                <th>Email</th>
                <th>Actions</th>
            </tr>
        </thead>
        <?php $clients = $objUser->getClients(); ?>
        <?php foreach($clients as $client) { ?>
            <tbody>
                <tr>
                    <td>
                        <div class="client-pic-ctn">
                            <?php echo $objUser->getClientInitials($client['name']); ?>
                        </div>
                    </td>
                    <td>
                        <?php echo $client['name']; ?><br>
                        <p class="badge badge-green">
                            <?php 
                                $location = $objLocation->getLocationById($client['location_id']); 
                                echo $location['name'];
                            ?> 
                        </p>
                    </td>
                    <td>
                        <?php echo $client['email']; ?>
                    </td>
                    <td>
                        <a href="/client/history?id=<?php echo $client['id']; ?>">
                            <div class="btn btn-icon"><i class="fa fa-history" aria-hidden="true"></i></div>
                        </a>
                        <a href="/client/edit?id=<?php echo $client['id']; ?>">
                            <div class="btn btn-icon"><i class="fa fa-pencil-square-o" aria-hidden="true"></i></div>
                        </a>
                        <input type="hidden" class="delete-id" value="<?php echo $client['id']; ?>" >
                        <a class="delete-confirmation">
                            <div class="btn btn-icon"><i class="fa fa-trash-o" aria-hidden="true"></i></div>
                        </a>
                    </td>
                </tr>
            </tbody>
        <?php } ?>
    </table>
</div>

<script type="text/javascript">
    
$(document).ready(function(){

    $('.delete-denied').click(function(e) {

        e.preventDefault();

        swal({
            title: "Unable to Delete!",
            text: "You cannot delete a location that has users assigned to it",
            icon: "error",
            timer: 5000,
        });

    });

    $('.delete-confirmation').click(function(e) {
        e.preventDefault();

        var deleteID = $(this).closest("tr").find('.delete-id').val();

        swal({
            title: "Are you sure?",
            text: "Once deleted, you will not be able to recover this client!",
            icon: "warning",
            buttons: true,
            dangerMode: true,
        })
        .then((willDelete) => {
            if (willDelete) {
            
                $.ajax({
                    type: "POST",
                    url: "/client/remove",
                    data: {
                        "id": deleteID,
                    },
                    success: function (response) {
                        
                        swal("Client Deleted Successfully!", {
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





