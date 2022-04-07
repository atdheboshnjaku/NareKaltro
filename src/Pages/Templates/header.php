<?php

use Fin\Narekaltro\App\Login;
use Fin\Narekaltro\App\Session;

error_reporting(E_ERROR | E_WARNING | E_PARSE);

?>
<!DOCTYPE html>
<html>
<head>
    <base href="/src/Pages/" />
    <meta charset="utf-8">
    <title>Fin NK</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3" crossorigin="anonymous">
    <link rel="stylesheet" type="text/css" href="Resources/css/main.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@5.10.1/main.min.css" integrity="sha256-16PDMvytZTH9heHu9KBPjzrFTaoner60bnABykjNiM0=" crossorigin="anonymous">
    <link rel="stylesheet" type="text/css" href="Resources/css/select2-min.css">
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.css">
    <!-- JS -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js" integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>
    <script src="https://code.jquery.com/ui/1.13.0-rc.3/jquery-ui.js" integrity="sha256-tYLuvehjddL4JcVWw1wRMB0oPSz7fKEpdZrIWf3rWNA=" crossorigin="anonymous"></script>
    <script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <!-- Calendar JS -->
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.10.1/main.min.js" integrity="sha256-XOMgUu4lWKSn8CFoJoBoGd9Q/OET+xrfGYSo+AKpFhE=" crossorigin="anonymous"></script>
</head>
<body>

<!-- Main body container -->
<div class="fluid-ctn">
    <!-- Side menu -->
    <?php

        $session = new Session();
        if($session->isLogged()) { ?>

        <aside>
        <div class="logo-ctn">
            asd
        </div>
        <div class="menu-item <?= (basename($_SERVER['PHP_SELF'])=="index.php") ? "active" : ""; ?>">
            <a class="menu-link" href="/">
                <span class="menu-icon"><i class="fa fa-th-large" aria-hidden="true"></i></span>
                <span class="menu-title">Dashboard</span>
            </a>
        </div>
        <div class="menu-item <?= (basename($_SERVER['PHP_SELF'])=="appointments.php") ? "active" : ""; ?>">
            <a class="menu-link" href="/appointments">
                <span class="menu-icon"><i class="fa fa-calendar-o" aria-hidden="true"></i></span>
                <span class="menu-title">Appointments</span>
            </a>
        </div>
        <div class="menu-item <?= (basename($_SERVER['PHP_SELF'])=="locations.php") ? "active" : ""; ?>">
            <a class="menu-link" href="/locations">
                <span class="menu-icon"><i class="fa fa-building-o" aria-hidden="true"></i></span>
                <span class="menu-title">Locations</span>
            </a>
        </div> 
        <div class="menu-item <?= (basename($_SERVER['PHP_SELF'])=="services.php") ? "active" : ""; ?>">
            <a class="menu-link" href="/services">
                <span class="menu-icon"><i class="fa fa-list-ul" aria-hidden="true"></i></span>
                <span class="menu-title">Services</span>
            </a>
        </div>        
        <div class="menu-item <?= (basename($_SERVER['PHP_SELF'])=="users.php") ? "active" : ""; ?>">
            <a class="menu-link" href="/users">
                <span class="menu-icon"><i class="fa fa-user-o" aria-hidden="true"></i></span>
                <span class="menu-title">Users</span>
            </a>
        </div>
        <div class="menu-item <?= (basename($_SERVER['PHP_SELF'])=="clients.php") ? "active" : ""; ?>">
            <a class="menu-link" href="/clients">
                <span class="menu-icon"><i class="fa fa-address-card-o" aria-hidden="true"></i></span>
                <span class="menu-title">Clients</span>
            </a>
        </div>
        <div class="menu-item <?= (basename($_SERVER['PHP_SELF'])=="reports.php") ? "active" : ""; ?>">
            <a class="menu-link" href="/reports">
                <span class="menu-icon"><i class="fa fa-area-chart" aria-hidden="true"></i></span>
                <span class="menu-title">Reports</span>
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
        <?php } ?>
        
    








