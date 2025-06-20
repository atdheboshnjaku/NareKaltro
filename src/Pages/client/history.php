<?php

use Fin\Narekaltro\App\Session;
use Fin\Narekaltro\App\Login;
use Fin\Narekaltro\App\Url;
use Fin\Narekaltro\App\Appointments;
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
						<p>
						<?php $location = $objLocation->getLocationById($history['location_id']); echo $location['name']; ?>
						</p>
						<?php if ($history['status'] == 0) : ?>
							<p class="badge" style="background:red;color:#fff;">Cancelled/Deleted</p>
						<?php endif; ?>
					</td>
					<td>
						<?php
							$services = explode(',', $history['service_id']);
							$serviceArray = [];
							$serviceCosts = $objAppointment->getServiceCostsByAppointment((int)$history['appointment_id'] ?? 0);

							foreach($services as $key => $value) {
								$service = $objAppointment->getService((int)$value);
								$service['price'] = $serviceCosts[$value] ?? null;
								$serviceArray[] = $service;
							}
						?>
						<?php foreach ($serviceArray as $service) : ?>
						<p class="badge" style="background:<?php echo $service['background']; ?>;color:<?php echo $service['color']; ?>">
						<?php echo $service['name']; ?>
						<?php if ($service['price']) echo ": €" . $service['price']; ?>
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