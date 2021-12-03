<?php 

use Fin\Narekaltro\App\Database;
use Fin\Narekaltro\App\Session;
use Fin\Narekaltro\App\Login;
use Fin\Narekaltro\App\User;
use Fin\Narekaltro\App\Appointments;

require_once("../../vendor/autoload.php");

$session = new Session();
if(!$session->isLogged()) {
    Login::redirectTo("/login");
}
$objAppointments = new Appointments();
$appointments = $objAppointments->getAppointmentsJSON();

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
</div>
    
<script type="text/javascript">



    document.addEventListener('DOMContentLoaded', function() {
        var calendarEl = document.getElementById('calendar');
        var calendar = new FullCalendar.Calendar(calendarEl, {
          initialView: 'dayGridMonth',
          headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay'
          },
          events: '/feed/feed.php'
          // display: 'block'
        });
        calendar.render();
      });


    // document.addEventListener('DOMContentLoaded', function() {
    // var calendarEl = document.getElementById('calendar');

    // var hangoutButton = $("#add-event");

    // var calendar = new FullCalendar.Calendar(calendarEl, {
    //     timeZone: 'UTC',
    //     selectable: true,
    //     headerToolbar: {
    //     left: 'prev,next today',
    //     center: 'title',
    //     right: 'dayGridMonth,timeGridWeek,timeGridDay'
    //     },
    //     editable: true,
    //     dayMaxEvents: true, // when too many events in a day, show the popover
    //     events: '/feed/feed.php', // retrieving appointments from a json feed
    //     dateClick: function(info) {
    //         alert('clicked ' + info.dateStr);
    //     },
    //     eventRender: function (info) {
    //         $(info.el).tooltip({ title: info.event.title });     
    //     }
        
    // });

    //     calendar.render();
    // });


</script>



<?php require_once("Templates/footer.php"); ?>

