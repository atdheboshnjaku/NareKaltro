<?php

use Fin\Narekaltro\App\Session;
use Fin\Narekaltro\App\Login;
use Fin\Narekaltro\App\User;
use Fin\Narekaltro\App\Form;
use Fin\Narekaltro\App\Validation;
use Fin\Narekaltro\App\Service;

require_once("../../vendor/autoload.php");

$objSession = new Session();
if(!$objSession->isLogged()) {
    Login::redirectTo("/login");
}

$objForm = new Form();
$objValidation = new Validation($objForm);
$objService = new Service();
$objUser = new User();
$services = $objService->getServices();
$serviceCount = $objService->serviceCount();

require_once("Templates/header.php");

?>



<div class="box">
    <div class="box-header">
        <div class="box-lf-ctn">
            <h2>Services</h2>
            <p><?php echo array_shift($serviceCount); ?> services in total</p>
        </div>
        <div class="box-rt-ctn">
            <a href="/service/add"><button class="action-btn align-middle"><i class="fa fa-plus-square-o" aria-hidden="true"></i>&nbsp; New Service</button></a>
        </div>
    </div>
    <table class="action-table align-middle">
        <thead>
            <tr>
                <th>Service</th>
                <th>Style</th>
                <th></th>
                <th>Actions</th>
            </tr>
        </thead>
        <?php foreach($services as $service) { ?>
            <tbody>
                <tr>
                    <td>
                        <?php echo $service['name']; ?>
                        <p>
                            
                        </p>
                    </td>
                    <td>
                        <p class="badge" style="background-color:<?php echo $service['background']; ?>; color:<?php echo $service['color']; ?>;">
                            <?php echo $service['name']; ?>
                        </p>
                    </td>
                    <td>
                        <p>
                       
                        </p>
                    </td>
                    <td>
                        <a href="/service/edit?id=<?php echo $service['id']; ?>">
                            <div class="btn btn-icon"><i class="fa fa-pencil-square-o" aria-hidden="true"></i></div>
                        </a> 
                        <input type="hidden" class="delete-id" value="<?php echo $service['id']; ?>" >
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
            text: "Once deleted, you will not be able to recover this service!",
            icon: "warning",
            buttons: true,
            dangerMode: true,
        })
        .then((willDelete) => {
            if (willDelete) {
            
                $.ajax({
                    type: "POST",
                    url: "/service/remove",
                    data: {
                        "id": deleteID,
                    },
                    success: function (response) {
                        
                        swal("Service Removed Successfully!", {
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





