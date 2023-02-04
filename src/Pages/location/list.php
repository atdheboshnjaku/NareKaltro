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
$objUser = new User();
$userAccount = $objUser->getUserAccountID($objSession->getUserId());
$objLocation = new Location();
$locationCount = $objLocation->locationCount($userAccount);


require_once("Templates/header.php");

?>



<div class="box">
    <div class="box-header">
        <div class="box-lf-ctn">
            <h2>Locations</h2>
            <p><?php echo array_shift($locationCount); ?> locations in total</p>
        </div>
        <div class="box-rt-ctn">
            <a href="/location/add"><button class="action-btn align-middle"><i class="fa fa-plus-square-o" aria-hidden="true"></i>&nbsp; New Location</button></a>
        </div>
    </div>
    <table class="action-table align-middle">
        <thead>
            <tr>
                <th>Location</th>
                <th># Employees</th>
                <th># Clients</th>
                <th>Actions</th>
            </tr>
        </thead>
        <?php $locations = $objLocation->getBusinessLocations($userAccount); ?>
        <?php foreach($locations as $location) { ?>
            <tbody>
                <tr>
                    <td>
                        <?php echo $location['name']; ?>
                        <p>
                            
                        </p>
                    </td>
                    <td>
                        <p class="badge badge-blue">
                        <?php 
                            $total = $objUser->employeeLocationCountById($location['id'], $userAccount); 
                            $emp = "";
                            $totalEmployees = array_pop($total);
                            if($totalEmployees <= 1) {
                                $emp = " Employee";
                            }
                            if($totalEmployees > 1) {
                                $emp = " Employees";
                            }
                            if($totalEmployees == 0) {
                                $emp = " Employees";
                            }
                            echo $totalEmployees . $emp;
                        ?>
                        </p>
                    </td>
                    <td>
                        <p class="badge badge-green">
                        <?php 
                            $total = $objUser->clientLocationCountById($location['id'], $userAccount); 
                            $cli = "";
                            $totalClients = array_pop($total);
                            if($totalClients <= 1) {
                                $cli = " Client";
                            }
                            if($totalClients > 1) {
                                $cli = " Clients";
                            }
                            if($totalClients == 0) {
                                $cli = " Clients";
                            }
                            echo $totalClients . $cli;
                        ?>
                        </p>
                    </td>
                    <td>
                        <a href="/location/edit?id=<?php echo $location['id']; ?>">
                            <div class="btn btn-icon"><i class="fa fa-pencil-square-o" aria-hidden="true"></i></div>
                        </a> 
                        <?php if(!$objUser->checkUserHasThisLocation($location['id'], $userAccount)) { ?>
                        <input type="hidden" class="delete-id" value="<?php echo $location['id']; ?>" >
                        <a class="delete-confirmation">
                            <div class="btn btn-icon"><i class="fa fa-trash-o" aria-hidden="true"></i></div>
                        </a>
                        <?php } else { ?>
                        <a class="delete-denied">
                            <div class="btn btn-icon"><i class="fa fa-trash-o" aria-hidden="true"></i></div>
                        </a>
                        <?php } ?>
                        
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
            text: "Once deleted, you will not be able to recover this location!",
            icon: "warning",
            buttons: true,
            dangerMode: true,
        })
        .then((willDelete) => {
            if (willDelete) {
            
                $.ajax({
                    type: "POST",
                    url: "/location/remove",
                    data: {
                        "id": deleteID,
                    },
                    success: function (response) {
                        
                        swal("Location Deleted Successfully!", {
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





