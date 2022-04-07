<?php 

use Fin\Narekaltro\App\Database;
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
if(!$session->isLogged()) {
    Login::redirectTo("/login");
}
$objAppointments = new Appointments();
$appointments = $objAppointments->getAppointmentsJSON();

$objForm = new Form();
$objValidation = new Validation($objForm);

$objLocation = new Location(); 
$locations = $objLocation->getBusinessLocations();
$objUser = new User();    
$userId = $session->getUserId();
$userLocationId = $objUser->getUserLocationID($userId);
$clients = $objUser->getClients();
$objServices = new Service();
$services = $objServices->getServices();

require_once("Templates/header.php");
?>

<div class="box">
    <div class="box-header">
        <div class="box-lf-ctn">
            <h2>Appointments from Lists</h2>
            <p>3 pending appointments in total</p>
        </div>
        <div class="box-rt-ctn">
            <!-- <a href="/appointment/add"><button id="add-event" class="action-btn align-middle"><i class="fa fa-plus-square-o" aria-hidden="true"></i>&nbsp; New Appointment</button> -->
        </div>
    </div>
    <div>
        <button></button>
    </div>
    <div id="calendar"></div>

    <!-- Modal: View Appointment-->
    <div class="modal fade" id="openappointment" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title" id="">Appointment Info</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
            <dl class="row">

                <dt class="col-sm-3">Appointment ID:</dt>
                <dd class="col-sm-9" id="id"></dd>

                <dt class="col-sm-3">Appointment Client:</dt>
                <dd class="col-sm-9" id="title"></dd>

                <dt class="col-sm-3">Appointment Location:</dt>
                <dd class="col-sm-9" id="location"></dd>

                <dt class="col-sm-3">Appointment Service:</dt>
                <dd class="col-sm-9" id="service"></dd>

                <dt class="col-sm-3">Appointment Start:</dt>
                <dd class="col-sm-9" id="start"></dd>

                <dt class="col-sm-3">Appointment End:</dt>
                <dd class="col-sm-9" id="end"></dd>

            </dl>
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
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">

        <form action="" method="post" class="add-form">
        
            <?php echo $objValidation->validate('location'); ?>
  
                <span>Location</span>
                <p>
                <select class="form-select" name="location_id" id="location_id">
                    <option disabled>Choose location</option>
                        <optgroup label="User location">
                            <?php foreach($locations as $location) { ?>
                                <option value="<?php echo $location['id']; ?>"
                                    <?php echo $objForm->stickySelect('location_id', $location['id'], $userLocationId['location_id']); ?>>
                                    <?php echo $location['name']; ?>
                                </option>
                            <?php } ?>
                        </optgroup>
                </select>
                </p>
                <span>Client</span>
                <p>
                <select class="csc-select client_id" name="client_id" id="client_id">
                    <option value="">Select Client</option>
                    <?php foreach($clients as $client) { ?>
                        <option value="<?php echo $client['id']; ?>" 
                            <?php echo $objForm->stickySelect('client_id', $client['id']); ?>>
                            <?php echo $client['name']; ?>
                        </option>
                    <?php } ?>    
                </select>
                </p>
                <span>Services</span>
                <p>
                <select class="csc-select" name="service_id" id="service_id">
                    <?php foreach($services as $service) { ?>
                        <option value="<?php echo $service['id']; ?>" 
                            <?php echo $objForm->stickySelect('service_id', $service['id']); ?>>
                            <?php echo $service['name']; ?>
                        </option>
                    <?php } ?>    
                </select>
                </p>
                <span>Appointment Start Date & Time</span>
                <p>
                    <input type="datetime-local" name="start_date" id="start_date" >
                </p>
                <span>Appointment Ending Date & Time</span>
                <p>
                    <input type="datetime-local" name="end_date" id="end_date" >
                </p>
            <p>
                <input type="submit" name="submit" id="submit" class="blue-btn alab" value="Add Appointment">
            </p>

        </form>
        
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
            <form action="" method="post" class="add-form">
            <div class="profile-card">
                <div class="client-profile-pic">
                    <!-- <input type="file" name="profile-image" accept="image/*" capture="user"> -->
                </div>
                <p>
                    <span>Phone</span>
                    <input type="number" name="number" placeholder="Client number" autocomplete="off">
                </p>
                <?php echo $objValidation->validate('country'); ?>
                <p>
                    <span>Country</span>
                    <select class="csc-select" name="country" id="country">
                        <option value="">Select Country</option>
                        <?php foreach($countries as $country) { ?>
                        <option value="<?php echo $country[$columnName['COLUMN_NAME']]; ?>"
                        >
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
                <input type="hidden" name="role_id" value="1">
                <p>

                </p>
                <?php echo $objValidation->validate('location_id'); ?>
                <p>
                    <span>Location</span>
                    <select name="location_id">
                        <option disabled>Choose location</option>
                        <optgroup label="User location">
                            <?php foreach($locations as $location) { ?>
                                <option value="<?php echo $location['id']; ?>"
                                    <?php echo $objForm->stickySelect('location_id', $location['id'], $userId); ?>>
                                    <?php echo $location['name']; ?>
                                </option>
                            <?php } ?>
                        </optgroup>
                    </select>
                </p>
                <?php echo $objValidation->validate('user_exists'); ?>
                <p>
                    <span>Email</span>
                    <input type="email" name="email" value="<?php echo $objForm->stickyText('email'); ?>" placeholder="" autocomplete="false">
                </p>
                <?php echo $objValidation->validate('name'); ?>
                <p>
                    <span>Client name</span>
                    <input type="hidden" name="status" value="1">
                    <input type="text" name="name" value="<?php echo $objForm->stickyText('name'); ?>" placeholder="" >
                </p>
                <p>
                    <input type="submit" name="submit" class="blue-btn alab" value="Add client">
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

    $('#client_id').select2({
        placeholder: "Select Client",
        dropdownParent: $('#addappointment'),
        language: {
            noResults: function() {
            return `<input value="Add Client" style="width: 100%" type="button"
            class="btn btn-primary" 
            onClick='addClient()'>`;
            }
         },
       
        escapeMarkup: function (markup) {
            return markup;
        }
    });

    function addClient() {
        $('#addclient').modal('show');
    }

    $('#service_id').select2({
        placeholder: 'Select Services',
        dropdownParent: $('#addappointment')
    });

    document.addEventListener('DOMContentLoaded', function() {
        var calendarEl = document.getElementById('calendar');
        var calendar = new FullCalendar.Calendar(calendarEl, {
            timeZone: 'UTC',
            initialView: 'dayGridMonth',
            dayMaxEventRows: true,
            editable: true,
            eventLimit: true,
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,timeGridDay'
            },
            events: '/feed/feed.php',
            eventClick: function(info) {

                info.jsEvent.preventDefault(); // don't let the browser navigate

                $('#openappointment #id').text(info.event.id);
                $('#openappointment #title').text(info.event.title);
                $('#openappointment #location').text(info.event.extendedProps.location);
                $('#openappointment #service').text(info.event.extendedProps.service);
                $('#openappointment #start').text(info.event.start);
                $('#openappointment #end').text(info.event.end);

                $('#openappointment').modal('show');

                
            },
            selectable: true,
            select: function(info) {
                
                $('#addappointment #start_date').val(info.start.toJSON().slice(0,19));

                $('#addappointment').modal('show');
            },            
            //display: 'block'
        });
        calendar.render();
    });

    $(function() {

        //twitter bootstrap script
        $("#submit").click(function(event) {

            var addAppointmentData = {
                location_id: $("#location_id").val(),
                client_id: $("#client_id").val(),
                service_id: $("#service_id").val(),
                start_date: $("#start_date").val(),
                end_date: $("#end_date").val(),
            };

            $.ajax({
                type: "POST",
                url: "appointment/add.php",
                data: addAppointmentData,
                dataType: "json",
                encode: true,
                }).done(function (data) {
                $('#addappointment').modal('hide');
                console.log(data);
            });

            //event.preventDefault();

        });

    });

    $(function() {
        
        //twitter bootstrap script
        $("#addnewclient").click(function(event) {

            var addAppointmentData = {
                location_id: $("#location_id").val(),
                client_id: $("#client_id").val(),
                service_id: $("#service_id").val(),
                start_date: $("#start_date").val(),
                end_date: $("#end_date").val(),
            };

            $.ajax({
                type: "POST",
                url: "appointment/add.php",
                data: addAppointmentData,
                dataType: "json",
                encode: true,
                }).done(function (data) {
                $('#addappointment').modal('hide');
                console.log(data);
            });

            //event.preventDefault();

        });

    });


</script>



<?php require_once("Templates/footer.php"); ?>

