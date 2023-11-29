<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header("Access-Control-Allow-Methods: POST, GET");
header("Access-Control-Allow-Headers: Content-Type");

use Fin\Narekaltro\App\Session;
use Fin\Narekaltro\App\Login;
use Fin\Narekaltro\App\User;
use Fin\Narekaltro\App\Appointments;
use Fin\Narekaltro\App\Form;
use Fin\Narekaltro\App\Validation;
use Fin\Narekaltro\App\Location;
use Fin\Narekaltro\App\Service;

require_once("../../vendor/autoload.php");

$session = new Session();
if (!$session->isLogged()) {
	Login::redirectTo("/login");
}

$objUser = new User();
$userId = $session->getUserId();
$userLocationId = $objUser->getUserLocationID($userId);
$userAccount = $objUser->getUserAccountID($userId);
//$userAccount = $objUser->getUserAccountID($userId) ? : '';
$clients = $objUser->getClients($userAccount);

$objAppointments = new Appointments();
$appointments = $objAppointments->getAppointmentsJSON($userAccount);
$upcomingAppointments = $objAppointments->numberOfUpcomingAppointments($userAccount);

$objForm = new Form();
$objValidation = new Validation($objForm);

$objLocation = new Location();
$countries = $objLocation->getCountries();
$locations = $objLocation->getBusinessLocations($userAccount);
$objServices = new Service();
$services = $objServices->getServices($userAccount);

require_once("Templates/header.php");

?>

<div class="box">
	<div class="box-header">
		<div class="box-lf-ctn">
			<h2>Appointments Calendar</h2>
			<p><?php echo array_shift($upcomingAppointments); ?> upcoming appointments in total</p>
		</div>
		<div class="box-rt-ctn">
			<!-- <a href="/appointment/add"><button id="add-event" class="action-btn align-middle"><i class="fa fa-plus-square-o" aria-hidden="true"></i>&nbsp; New Appointment</button> -->
		</div>
	</div>
	<br>
	<div>
		<button class="hideme">Add Appointment</button>
	</div>
	<br>
	<div id="calendar"></div>

	<!-- Modal: View/Edit/Delete Appointment -->
	<div class="modal fade" id="openappointment" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
		<div class="modal-dialog modal-dialog-centered">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title" id="">Appointment Info/Edit</h5>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
				</div>
				<div class="modal-body">

					<form id="editevent" action="" method="post" class="add-form">

						<span>Client</span>
						<p>
							<input type="hidden" name="id" id="id" disabled>
							<input type="text" name="title" id="title" disabled>
						</p>

						<?php echo $objValidation->validate('location'); ?>

						<span>Location</span>
						<p>
							<select class="form-select" name="e_location_id" id="e_location_id">
								<option disabled>Choose location</option>
								<optgroup label="User location">
									<?php foreach ($locations as $location) { ?>
										<option value="<?php echo $location['id']; ?>" <?php echo $objForm->stickySelect('e_location_id', $location['id']); ?>>
											<?php echo $location['name']; ?>
										</option>
									<?php } ?>
								</optgroup>
							</select>
						</p>
						<span>Services</span>
						<p>
							<select multiple class="csc-select required-field" name="e_service_id" id="e_service_id">
								<?php foreach ($services as $service) { ?>
									<option value="<?php echo $service['id']; ?>" <?php // echo $objForm->stickySelect('e_service_id', $service['id']);
																					?>>
										<?php echo $service['name']; ?>
									</option>
								<?php } ?>
							</select>
							<span class="required-warning warn">Please choose at least 1 service</span>
						</p>
						<span>Appointment Start Date & Time</span>
						<p>
							<input type="datetime-local" name="e_start_date" id="e_start_date">
						</p>
						<span>Appointment Ending Date & Time</span>
						<p>
							<input type="datetime-local" name="e_end_date" id="e_end_date">
						</p>
						<span>Appointment Notes</span>
						<p>
							<textarea rows="4" name="appointment_notes" id="appointment_notes"></textarea>
						</p>
						<p>
							<input type="button" name="submitAppUpdate" id="submitAppUpdate" class="blue-btn alab" value="Update">
							<input type="hidden" class="delete-id" id="id">
							<input type="button" name="submitRemove" id="submitRemove" class="red-btn fl-rt" value="Delete">
						</p>

					</form>
					<!-- <p>
						<input type="submit" name="submitAppUpdate" id="submitAppUpdate" class="blue-btn alab" value="Update">
						<input type="hidden" class="delete-id" id="id">
						<input type="submit" name="submitRemove" id="submitRemove" class="red-btn fl-rt" value="Delete">
					</p> -->

				</div>
			</div>
		</div>
	</div>

	<!-- Modal: Add Appointment -->
	<div class="modal fade" id="addappointment" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
		<div class="modal-dialog modal-dialog-centered">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title" id="">Create Appointment</h5>
					<button type="button" class="btn-close del-ls" data-bs-dismiss="modal" aria-label="Close"></button>
				</div>
				<div class="modal-body">

					<form id="addevent" action="" method="post" class="add-form">

						<?php echo $objValidation->validate('location'); ?>

						<span>Location</span>
						<p>
							<select class="form-select" name="location_id" id="location_id">
								<option disabled>Choose location</option>
								<optgroup label="User location">
									<?php foreach ($locations as $location) { ?>
										<option value="<?php echo $location['id']; ?>" <?php echo $objForm->stickySelect('location_id', $location['id'], $userLocationId['location_id']); ?>>
											<?php echo $location['name']; ?>
										</option>
									<?php } ?>
								</optgroup>
							</select>
						</p>
						<span>Client</span>
						<p>
							<select class="csc-select client_id required-field" name="client_id" id="client_id" required>
								<option value="">Select Client</option>
								<?php foreach ($clients as $client) { ?>
									<option value="<?php echo $client['id']; ?>" <?php echo $objForm->stickySelect('client_id', $client['id']); ?>>
										<?php echo $client['name']; ?>
									</option>
								<?php } ?>
							</select>
							<span class="required-warning warn">Please choose a client</span>
						</p>
						<?php echo $objValidation->validate('appointment_service'); ?>
						<span>Services</span>
						<p>
							<select class="csc-select required-field" name="service_id" id="service_id" multiple required>
								<?php foreach ($services as $service) { ?>
									<option value="<?php echo $service['id']; ?>" <?php echo $objForm->stickySelect('service_id', $service['id']); ?>>
										<?php echo $service['name']; ?>
									</option>
								<?php } ?>
							</select>
							<span class="required-warning warn">Please choose at least 1 service</span>
						</p>
						<span>Appointment Start Date & Time</span>
						<p>
							<input type="datetime-local" name="start_date" id="start_date">
						</p>
						<span>Appointment Ending Date & Time</span>
						<p>
							<input type="datetime-local" name="end_date" id="end_date">
						</p>
						<span>Appointment Notes</span>
						<p>
							<textarea rows="4" name="a_appointment_notes" id="a_appointment_notes"></textarea>
						</p>
						<p>
							<!-- <input type="button" name="submitApp" id="submitApp" class="blue-btn alab del-ls" value="Add Appointment"> -->
							<!-- <button type="submit" name="submitApp" id="submitApp" class="blue-btn alab del-ls" value="Add Appointment">Add Appointment</button> -->
						</p>

					</form>
					<input type="submit" name="submitApp" id="submitApp" class="blue-btn alab del-ls" value="Add Appointment">
				</div>
			</div>
		</div>
	</div>

	<!-- Modal: Add Client-->
	<div class="modal fade" id="addclient" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
		<div class="modal-dialog modal-lg modal-dialog-centered modal-lg">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title" id="">Add Client</h5>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
				</div>
				<div class="modal-body">
					<br>
					<br>
					<form id="addclientform" action="" method="post" class="add-form">
						<div class="profile-card">

							<div class="client-profile-pic">

							</div>
							<p>
								<span>Phone</span>
								<input type="number" name="c_number" placeholder="Client number" autocomplete="off" id="c_number">
							</p>
							<?php echo $objValidation->validate('country'); ?>
							<p>
								<span>Country</span>
								<select class="csc-select" name="country" id="country">
									<option value="">Select Country</option>
									<?php foreach ($countries as $country) { ?>
										<option value="<?php echo $country['id']; ?>">
											<?php echo $country['name']; ?>
										</option>
									<?php } ?>
								</select>
							</p>
							<?php echo $objValidation->validate('state'); ?>
							<p>
								<span>State</span>
								<select class="csc-select" name="state" id="state">
									<option value="">Select State</option>
								</select>
							</p>
							<p>
								<span>City</span>
								<select class="csc-select" name="city" id="city">
									<option value="">Select City</option>
								</select>
							</p>
						</div>

						<div class="profile-info">
							<input type="hidden" name="c_role_id" id="c_role_id" value="1">
							<p>

							</p>
							<?php echo $objValidation->validate('location_id'); ?>
							<p>
								<span>Location</span>
								<select name="c_location_id" id="c_location_id">
									<option disabled>Choose location</option>
									<optgroup label="User location">
										<?php foreach ($locations as $location) { ?>
											<option value="<?php echo $location['id']; ?>" <?php echo $objForm->stickySelect('c_location_id', $location['id'], $userLocationId); ?>>
												<?php echo $location['name']; ?>
											</option>
										<?php } ?>
									</optgroup>
								</select>
							</p>
							<?php echo $objValidation->validate('user_exists'); ?>
							<p>
								<span>Email</span>
								<input type="email" name="c_email" value="<?php echo $objForm->stickyText('c_email'); ?>" placeholder="" autocomplete="false" id="c_email">
							</p>
							<?php echo $objValidation->validate('name'); ?>
							<p>
								<span>Client name</span>
								<input type="hidden" name="c_status" id="c_status" value="1">
								<input type="text" name="c_name" value="<?php echo $objForm->stickyText('c_name'); ?>" placeholder="" id="c_name">
							</p>
							<p>
								<input type="button" name="submitAddClient" id="submitAddClient" class="blue-btn alab" value="Add client">
							</p>
						</div>

					</form>
					<br>
					<br>
				</div>
			</div>
		</div>
	</div>

</div>
<!-- Appointments FullCalendar -->
<script type="text/javascript">
	$(function() {
		var newClientCreated = localStorage.getItem("newlyCreatedClient");
		if (newClientCreated) {
			$('#addappointment').modal('show');
			$("#client_id").val(newClientCreated).trigger('change');
		}
	});

	// Adding global requirement status trigger
	$('.required-field').change(function() {
		var $field = $(this);
		var $warn = $field.siblings('.required-warning');

		if ($field.val() != '') {
			$warn.removeClass("visible");
		}
	});


	$('#client_id').select2({
		placeholder: "Select Client",
		dropdownParent: $('#addappointment'),
		language: {
			noResults: function() {
				return `<input value="Add Client" style="width: 100%" type="button"
				class="btn blue-btn wt-on-hv" id="ACB"
				onClick='addClient()'>`;
			}
		},

		escapeMarkup: function(markup) {
			return markup;
		}
	});

	function addClient() {
		$('#addappointment').modal('hide');
		// $('.required-warning').hide();
		// $('#addappointment').find('form')[0].reset();
		$('#addclient').modal('show');
	}

	$('#service_id').select2({
		placeholder: 'Select Services1',
		dropdownParent: $('#addappointment'),
	});

	$('#e_service_id').select2({
		placeholder: 'Select Services2',
		//dropdownParent: $('#addappointment'),
	});

	// $('#service_id').attr("required", "required");
	// $('#e_service_id').attr("required", "required");

	function getTimezone() {
		return Intl.DateTimeFormat().resolvedOptions().timeZone;
	}

	document.addEventListener('DOMContentLoaded', function() {

		var calendarEl = document.getElementById('calendar');
		var calendar = new FullCalendar.Calendar(calendarEl, {
			//timeZone: 'UTC',
			initialView: 'dayGridMonth',
			dayMaxEventRows: true,
			editable: true,
			// longPressDelay: 0.5,
			eventTimeFormat: {
				hour: 'numeric',
				minute: '2-digit'
			},
			headerToolbar: {
				left: 'prev,next today',
				center: 'title',
				right: 'dayGridMonth,timeGridWeek,timeGridDay,listWeek'
			},
			events: '/feed/feed.php',
			eventClick: function(info) {

				info.jsEvent.preventDefault(); // don't let the browser navigate
				var tz = calendar.getOption('timeZone');
				console.log("Timezone is: " + tz);
				$('#openappointment #id').text(info.event.id);
				$('#openappointment #id').val(info.event.id);
				$('#openappointment #title').val(info.event.title);

				$('#openappointment #title').text(info.event.title);

				$('#openappointment #location_id').text(info.event.extendedProps.location);
				$('#openappointment #e_location_id').val(info.event.extendedProps.location_id);

				$('#openappointment #service_id').text(info.event.extendedProps.service);
				$('#openappointment #e_service_id').val(info.event.extendedProps.service_id).trigger('change');

				ids = info.event.extendedProps.service_id;
				newA = ids.split(",");
				$('#openappointment #e_service_id').val(newA).trigger('change');
				console.log('IDs: ' + newA);

				$('#openappointment #start_date').text(info.event.start);
				$('#openappointment #e_start_date').val(info.event.start.toJSON().slice(0, 19));

				$('#openappointment #end_date').text(info.event.end);

				if (info.event.end) {
					$('#openappointment #e_end_date').val(info.event.end.toJSON().slice(0, 19));
					console.log(info.event.end);
				} else {
					$('#openappointment #e_end_date').val(info.event.end);
				}

				$('#openappointment #appointment_notes').text(info.event.extendedProps.notes);
				$('#openappointment #appointment_notes').val(info.event.extendedProps.notes);

				$('#openappointment').modal('show');

			},
			selectable: true,
			select: function(info) {

				$('#addappointment #start_date').val(info.start.toJSON().slice(0, 19));

				$('#addappointment').modal('show');

				var newClientID = localStorage.getItem("newlyCreatedClient");
				$("#client_id").val(newClientID).trigger('change');
			},
			eventDrop: function(info) {

				var appointment_id = info.event.id;
				var start_date = info.event.start.toJSON().slice(0, 19);
				if (info.event.end) {
					var end_date = info.event.end.toJSON().slice(0, 19);
				}

				$.ajax({
					type: "POST",
					url: "/src/Pages/appointment/editStartDate.php",
					data: {
						appointment_id: appointment_id,
						start_date: start_date,
						end_date: end_date
					},
					success: function(data) {
						console.log(data);
						calendar.refetchEvents();
					}

				});


			},
			eventDisplay: 'block'
		});
		calendar.render();

		calendar.setOption('timeZone', Intl.DateTimeFormat().resolvedOptions().timeZone);

	});

	$(function() {

		//twitter bootstrap script adding new appointments
		$("#submitApp").click(function(event) {

			//event.preventDefault();
			originalArray = $("#service_id").val();
			separator = ',';
			implodedArray = originalArray.join(separator);

			var addAppointmentData = {
				location_id: $("#location_id").val(),
				client_id: $("#client_id").val(),
				// service_id: $("#service_id").val(),
				implodedArray,
				//service_id: originalArray.join(separator),
				start_date: $("#start_date").val(),
				end_date: $("#end_date").val(),
				a_appointment_notes: $("#a_appointment_notes").val()
			};

			// $.ajax({
			//     type: "POST",
			//     url: "/src/Pages/appointment/add.php",
			//     data: addAppointmentData,
			//     dataType: "json",
			//     encode: true,
			//     }).done(function (data) {
			//     if ($('#addappointment').hasClass('show')) {
			//         $('#addappointment').modal('hide');
			//     }
			//     //$('#addappointment').modal('hide');
			//     console.log(data);
			//     calendar.refetchEvents();
			//     location.reload();
			// });

			if ($("#service_id").val() == '') {
				event.preventDefault();
				$('.required-warning').addClass("visible");
				return false;
			}

			$.ajax({
				type: "POST",
				url: "/src/Pages/appointment/add.php",
				data: addAppointmentData,
				dataType: "json",
				encode: true,
				cache: false,
			}).then(function(data) {
				console.log(data);
				calendar.refetchEvents();
				//location.reload();
			}).catch(function(jqXHR, textStatus, errorThrown) {
				console.log(xhr.responseText);
				alert('Error: Appointment was not added successfully!');
			}).always(function() {
				if ($('#addappointment').hasClass('show')) {
					$('#addappointment').modal('hide');
				}
				setTimeout(function() {
					location.reload();
				}, 500); // Wait for 500ms before reloading the page
			});


		});


	});

	// Triggering the deleteLocalStorage function in case the client is not created and the back button is clicked
	$('.del-ls').click(function() {
		deleteLocalStorage();
	});

	// This function is also called by PHP using script tags when the create client form is successfully submitted
	function deleteLocalStorage() {
		var country_id = localStorage.getItem("select2CountryValue");
		var state_id = localStorage.getItem("select2StateValue");
		var newClientID = localStorage.getItem("newlyCreatedClient");

		localStorage.removeItem('select2CountryValue');
		localStorage.removeItem('select2StateValue');
		localStorage.removeItem('newlyCreatedClient');
	}

	$(function() {

		//twitter bootstrap script updating appointment
		$("#submitAppUpdate").click(function(event) {

			//event.preventDefault();
			originalArray = $("#e_service_id").val();
			separator = ',';
			implodedServicesArray = originalArray.join(separator);

			var updateAppointmentData = {
				id: $("#id").val(),
				e_location_id: $("#e_location_id").val(),
				//
				implodedServicesArray,
				e_start_date: $("#e_start_date").val(),
				e_end_date: $("#e_end_date").val(),
				appointment_notes: $("#appointment_notes").val(),
			};

			// $.ajax({
			//     type: "POST",
			//     url: "/src/Pages/appointment/edit.php",
			//     data: updateAppointmentData,
			//     dataType: "json",
			//     encode: true,
			// }).done(function(data) {
			//     $('#openappointment').modal('hide');
			//     console.log(data.error);
			//     calendar.refetchEvents();
			// }).fail(function(jqXHR, textStatus, errorThrown) {
			//     console.log(jqXHR.responseText);
			//     alert('Error: Appointment not edited!');
			// });

			if ($("#e_service_id").val() == '') {
				event.preventDefault();
				$('.required-warning').addClass("visible");
				return false;
			}

			$.ajax({
				type: "POST",
				url: "/src/Pages/appointment/edit.php",
				data: updateAppointmentData,
				dataType: "json",
				encode: true,
				cache: false,
			}).then(function(data) {
				console.log(data);
				calendar.refetchEvents();
				//location.reload();
			}).catch(function(jqXHR, textStatus, errorThrown) {
				console.log(jqXHR.responseText);
				//alert('Error: Appointment was not edited successfully!');
			}).always(function() {
				if ($('#openappointment').hasClass('show')) {
					$('#openappointment').modal('hide');
				}
				setTimeout(function() {
					location.reload();
				}, 500); // Wait for 500ms before reloading the page
			});

		});

	});

	$('#submitRemove').click(function(e) {
		e.preventDefault();

		var id = $("#id").val();

		swal({
				title: "Are you sure?",
				text: "Once deleted, you will not be able to recover this appointment!",
				icon: "warning",
				buttons: true,
				dangerMode: true,
			})
			.then((willDelete) => {
				if (willDelete) {

					$.ajax({
						type: "POST",
						url: "/src/Pages/appointment/remove.php",
						data: {
							"id": id,
						},
						success: function(response) {
							console.log(response);
							swal("Appointment Deleted Successfully!", {
								icon: "success",
							}).then((result) => {
								location.reload();
							});

						}
					});

				}
			});

	});

	$(function() {

		//twitter bootstrap script updating appointment, from click to submit test
		$("#submitAddClient").click(function(event) {

			//event.preventDefault();

			var addClientData = {
				c_number: $("#c_number").val(),
				country: $("#country").val(),
				state: $("#state").val(),
				city: $("#city").val(),
				c_role_id: $("#c_role_id").val(),
				c_location_id: $("#c_location_id").val(),
				c_email: $("#c_email").val(),
				c_status: $("#c_status").val(),
				c_name: $("#c_name").val()
			};

			// $.ajax({
			//     type: "POST",
			//     url: "/src/Pages/client/addFromAppointment.php",
			//     data: addClientData,
			//     dataType: "json",
			//     encode: true,
			// }).done(function(data) {
			//     $('#addclient').modal('hide');
			//     var newClientID = data;
			//     localStorage.setItem("newlyCreatedClient", newClientID);
			//     console.log(data);
			//     calendar.refetchEvents();
			//     setTimeout(function() {
			//         location.reload();
			//     }, 500);
			// });

			// if ($("#client_id").val() == '') {
			//     event.preventDefault();
			//     $('.required-warning').addClass("visible");
			//     return false;
			// }

			$.ajax({
				type: "POST",
				url: "/src/Pages/client/addFromAppointment.php",
				data: addClientData,
				dataType: "json",
				encode: true,
				cache: false,
			}).done(function(data) {
				console.log(data);
				//calendar.refetchEvents();
				//location.reload();
				var newClientID = data;
				localStorage.setItem("newlyCreatedClient", newClientID);
			}).fail(function(jqXHR, textStatus, errorThrown) {
				console.log(jqXHR.responseText);
				//alert('Error: Client was not added successfully!');
			}).always(function() {
				if ($('#addclient').hasClass('show')) {
					$('#addclient').modal('hide');
				}
				setTimeout(function() {
					location.reload();
				}, 500); // Wait for 500ms before reloading the page
			});

		});

		var country_id = localStorage.getItem("select2CountryValue");
		var state_id = localStorage.getItem("select2StateValue");
		var page_load = true; //added this

		$('select[name="country"]').on('change', function() {
			var country_id = $(this).val();
			localStorage.setItem("select2CountryValue", country_id);
			if (country_id) {
				$.ajax({
					url: "/src/Pages/world/getStates.php",
					type: "GET",
					data: {
						"country_id": country_id
					},
					dataType: "json",
					//contentType: "application/json; charset=utf-8",
					success: function(data) {
						console.log(data);
						$('select[name="state"]').empty();
						$('select[name="state"]').append('<option value="0">Select State</option>');
						$.each(JSON.parse(data), function(key, value) {
							$('select[name="state"]').append('<option value="' + value.id + '">' + value.name + '</option>');
						});
						//check if the change is called on page load
						if (page_load == true) {
							$('#state').val(state_id).trigger('change'); //assign slected value after element option is added in dom
							page_load = false; //adding this so that next time this doesn't get execute
						}
					}
				});
			} else {
				$('select[name="state"]').empty();
			}
		});

		$('#country').val(country_id).trigger('change');

		$('select[name="state"]').on('change', function() {

			var country_id = $('#country').val();
			var state_id = $(this).val();
			localStorage.setItem("select2StateValue", state_id);
			if (state_id) {
				$.ajax({
					url: "/src/Pages/world/getCities.php",
					type: "GET",
					data: {
						"country_id": country_id,
						"state_id": state_id
					},
					dataType: "json",
					success: function(data) {
						console.log(data);
						$('select[name="city"]').empty();
						$('select[name="city"]').append('<option value="0">Select City</option>');
						$.each(JSON.parse(data), function(key, value) {
							$('select[name="city"]').append('<option value="' + value.id + '">' + value.name + '</option>');
						});
					}
				});
			} else {
				$('select[name="city"]').empty();
			}
		});

	});
</script>



<?php require_once("Templates/footer.php"); ?>