<?php

use Fin\Narekaltro\App\Session;
use Fin\Narekaltro\App\Login;
use Fin\Narekaltro\App\Url;
use Fin\Narekaltro\App\Appointments;
use Fin\Narekaltro\App\User;
use Fin\Narekaltro\App\Form;
use Fin\Narekaltro\App\Validation;
use Fin\Narekaltro\App\Location;

require_once("../../../vendor/autoload.php");

$objSession = new Session();
if(!$objSession->isLogged()) {
	Login::redirectTo("/login");
}

$id = Url::getParam("id");

$objAppointment = new Appointments();
$userHistory = $objAppointment->getClientHistory($id);
$appointmentCount = $objAppointment->totalClientAppointments($id);
$objLocation = new Location();


require_once("../Templates/header.php");

?>

<div class="box">
	<div class="box-header">
		<div class="box-lf-ctn">
			<h2>History: </h2>
			<p><?php echo $appointmentCount['total']; ?> appointments in total</p>
		</div>
		<div class="box-rt-ctn">

		</div>
	</div>
	<table class="action-table align-middle history-table">
		<thead>
			<tr>
				<th>Location</th>
				<th>Service</th>
				<th>Start</th>
				<th>End</th>
				<th class="align-left">Notes</th>
			</tr>
		</thead>
		<?php foreach($userHistory as $history) { ?>
			<tbody>
				<tr>
					<td>
						<?php $location = $objLocation->getLocationById($history['location_id']); echo $location['name']; ?>
						</p>
					</td>
					<td>
						<?php
							$services = explode(',', $history['service_id']);//var_dump($history);
							$serviceArray = [];

							foreach($services as $key => $value) {
								$serviceArray[] = $objAppointment->getService($value);
							}
						?>
						<?php foreach ($serviceArray as $service) : ?>
						<p class="badge" style="background:<?php echo $service['background']; ?>;color:<?php echo $service['color']; ?>">
						<?php echo $service['name']; ?>
						</p>
						<?php endforeach; ?>
					</td>
					<td>
					<?php echo $history['start_date']; ?>
					</td>
					<td>
					<?php echo $history['end_date']; ?>
					</td>
					<td class="align-left">
					<?php echo $history['appointment_notes']; ?>
					</td>
				</tr>
			</tbody>
		<?php } ?>
	</table>
</div>
<?php require_once("../Templates/footer.php"); ?>