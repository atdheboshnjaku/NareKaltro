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
    Login::redirectTo("login");
}

$objForm = new Form();
$objValidation = new Validation($objForm);
$objLocation = new Location();
$locationCount = $objLocation->locationCount();

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
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <?php $locations = $objLocation->getBusinessLocations(); ?>
        <?php foreach($locations as $location) { ?>
            <tbody>
                <tr>
                    <td>
                        <?php echo $location['name']; ?>
                        <p>3 Employees</p>
                    </td>
                    <td><p class="badge badge-active">Active</p></td>
                    <td>
                        <a href="/location/edit?id=<?php echo $location['location_id']; ?>">
                            <div class="btn btn-icon"><i class="fa fa-pencil-square-o" aria-hidden="true"></i></div>
                        </a> 
                        <a href="/location/remove?id=<?php echo $location['location_id']; ?>">
                            <div class="btn btn-icon"><i class="fa fa-trash-o" aria-hidden="true"></i></div>
                        </a>
                    </td>
                </tr>
            </tbody>
        <?php } ?>
    </table>
</div>

<?php require_once("Templates/footer.php"); ?>





