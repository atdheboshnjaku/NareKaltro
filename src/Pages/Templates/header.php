<?php

use Fin\Narekaltro\App\Login;
use Fin\Narekaltro\App\Session;

//if(isset($_SESSION['username'])) { echo $_SESSION['username']; }

//error_reporting(E_ERROR | E_WARNING | E_PARSE);

?>
<!DOCTYPE html>
<html>
<head>
    <base href="/src/Pages/" />
    <meta charset="utf-8">
    <title>Fin NK</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- CSS -->
    <link rel="stylesheet" type="text/css" href="Resources/css/main.css">
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.css">
    <!-- JS -->
    <script src="https://code.jquery.com/jquery-3.6.0.js" integrity="sha256-H+K7U5CnXl1h5ywQfKtSj8PCmoN9aaq30gDh27Xc0jk=" crossorigin="anonymous"></script>
</head>
<body>

<!-- Main body container -->
<div class="fluid-ctn">
    <!-- Side menu -->
    <!-- <?php

        // $session = new Session();
        // if($session->isLogged()) { ?> -->

        <aside>
        <div class="logo-ctn">
            asd
        </div>
        <div class="menu-item <?= (basename($_SERVER['PHP_SELF'])=="index.php") ? "active" : ""; ?>">
            <a class="menu-link" href="/">
                <span class="menu-icon"><i class="fa fa-calendar-minus-o" aria-hidden="true"></i></span>
                <span class="menu-title">Dashboard</span>
            </a>
        </div>
        <div class="menu-item <?= (basename($_SERVER['PHP_SELF'])=="locations.php") ? "active" : ""; ?>">
            <a class="menu-link" href="/locations">
                <span class="menu-icon"><i class="fa fa-building-o" aria-hidden="true"></i></span>
                <span class="menu-title">Locations</span>
            </a>
        </div>        
        <div class="menu-item <?= (basename($_SERVER['PHP_SELF'])=="users.php") ? "active" : ""; ?>">
            <a class="menu-link" href="/users">
                <span class="menu-icon"><i class="fa fa-user-o" aria-hidden="true"></i></span>
                <span class="menu-title">Users</span>
            </a>
        </div>
        <div class="menu-item">
            <a class="menu-link" href="/logout">
                <span class="menu-icon"><i class="fa fa-sign-out" aria-hidden="true"></i></span>
                <span class="menu-title">Logout</span>
            </a>
        </div>

        </aside>
        <!-- View -->
        <div class="app-view">
            <div class="top-bar">
                sdsdfs
            </div>
        <!-- <?php } ?> -->
        
    








