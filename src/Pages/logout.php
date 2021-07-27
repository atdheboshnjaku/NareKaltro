<?php

use Fin\Narekaltro\App\Session;
use Fin\Narekaltro\App\Login;

require_once("../../vendor/autoload.php");

$session = new Session();
$session->logout();
Login::redirectTo("/");

