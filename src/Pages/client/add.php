<?php
 
use Fin\Narekaltro\App\Session;
use Fin\Narekaltro\App\Login;
use Fin\Narekaltro\App\User;
use Fin\Narekaltro\App\Form;
use Fin\Narekaltro\App\Validation;
use Fin\Narekaltro\App\Location;

require_once("../../../vendor/autoload.php");

$objSession = new Session();
if(!$objSession->isLogged()) {
    Login::redirectTo("/login");
}

$userId = $objSession->getUserId();

$objForm = new Form();
$objValidation = new Validation($objForm);
$objUser = new User();
$userCount = $objUser->userCount();
$objLocation = new Location();
$locations = $objLocation->getBusinessLocations();
$countries = $objLocation->getCountries();
$columnName = $objLocation->getColumnName();

if($objForm->isPost("name")) {

    $objValidation->expected = [
        "role_id", 
        "location_id", 
        "name", 
        "email", 
        "number",
        "password",
        "country",
        "state",
        "city", 
        "status"
    ];

    $objValidation->required = ["location_id", "name", "country"];

    if(!empty($objForm->getPost("email"))) 
    {
        $objValidation->special = ["email" => "email"];

        $email = $objForm->getPost("email");
        $existingUser = $objUser->getUserByEmail($email);

        if(!empty($existingUser)) {
            $objValidation->addToErrors("user_exists");
        }         
    }
    

    if($objValidation->isValid()) {
        echo "<pre>";
        print_r($objValidation->post);
        echo "</pre>";
        if($objUser->createUser($objValidation->post)) {
            echo "<script type='text/javascript'> 
                function deleteLocalStorage() {
                    var country_id = localStorage.getItem('select2CountryValue');
                    var state_id = localStorage.getItem('select2StateValue');

                    localStorage.removeItem('select2CountryValue');
                    localStorage.removeItem('select2StateValue');
                }
              </script>";
            echo "<script type='text/javascript'> deleteLocalStorage(); </script>";
            Login::redirectTo("/clients");
        } else {
            Login::redirectTo("/error");
        }

    } 

}

require_once("../Templates/header.php");

?>

<div class="profile-box-ctn">
    <div class="profile-box-header white-bg">
        <div class="box-lf-ctn">
            <h2>Clients</h2>
            <p>Add your new client</p>
        </div>
        <div class="box-rt-ctn">
            <a href="/clients"><button class="action-btn align-middle del-ls"><i class="fa fa-arrow-circle-o-left" aria-hidden="true"></i>&nbsp; Go Back</button></a>
        </div>
    </div>
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
   
</div>

<script>
    $(document).ready(function() {

    var country_id = localStorage.getItem("select2CountryValue");
    var state_id = localStorage.getItem("select2StateValue");
    var page_load = true; //added this 

    // Triggering the deleteLocalStorage function in case the client is not created and the back button is clicked
    $('.del-ls').click(function() {
        deleteLocalStorage();
    });

    // This function is also called by PHP using script tags when the create client form is successfully submitted
    function deleteLocalStorage() {
        var country_id = localStorage.getItem("select2CountryValue");
        var state_id = localStorage.getItem("select2StateValue");

        localStorage.removeItem('select2CountryValue');
        localStorage.removeItem('select2StateValue');
    }

    //$('.csc-select').select2(); 
    $('#country').select2({
        placeholder: 'Select Country'
    });

    $('#state').select2({
        placeholder: 'Select State/Region'
    });

    $('#city').select2({
        placeholder: 'Select City'
    });

    $('select[name="country"]').on('change',function() {
        var country_id= $(this).val();
        localStorage.setItem("select2CountryValue", country_id);
        if (country_id) {
            $.ajax({
            url: "/src/Pages/world/getStates.php",
            type: "GET",
            data: {'country_id':country_id},
            dataType: "json",
            success: function(data) {
                console.log(data);
                $('select[name="state"]').empty();
                $('select[name="state"]').append('<option value="">Select State</option>');
                $.each(JSON.parse(data), function(key,value) {
                    $('select[name="state"]').append('<option value="'+value.id+'">'+value.name+'</option>');
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

    $('select[name="state"]').on('change',function() {
        
        var country_id = $('#country').val();
        var state_id = $(this).val();
        localStorage.setItem("select2StateValue", state_id);
        if (state_id) {
        $.ajax({
            url: "/world/getCities.php",
            type: "GET",
            data: {'country_id': country_id, 'state_id': state_id},
            dataType: "json",
            success: function(data) {
                console.log(data);
                $('select[name="city"]').empty();
                $('select[name="city"]').append('<option value="">Select City</option>');
                $.each(JSON.parse(data),function(key,value) {
                    $('select[name="city"]').append('<option value="'+value.id+'">'+value.name+'</option>');
                });
            }
        });
        }else {
             $('select[name="city"]').empty();
       }
    });

    });

    


</script>

<?php require_once("../Templates/footer.php"); ?>