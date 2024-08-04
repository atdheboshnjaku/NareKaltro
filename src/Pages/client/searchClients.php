<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

use Fin\Narekaltro\App\User;
use Fin\Narekaltro\App\Login;
use Fin\Narekaltro\App\Session;

require_once("../../../vendor/autoload.php");

$objSession = new Session();
if (!$objSession->isLogged()) {
	Login::redirectTo("/login");
}

$objUser = new User();
$userAccount = $objUser->getUserAccountID($objSession->getUserId());

// Get the search query
$searchQuery = $_POST['query'] ?? '';

// Perform the database query to get clients matching the search query
$clients = $objUser->searchClients($userAccount, $searchQuery);

// Return the results as a JSON response
echo json_encode($clients);
