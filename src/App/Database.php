<?php

declare(strict_types = 1);
namespace Fin\Narekaltro\App;

use Dotenv\Dotenv;

class Database
{

    public function __construct() 
    {
        // Initializing a connection to the Database
        $this->connect();
    
    }

    public function connect()  
    {
        // Using vlucas/phpdotenv package to load the environment (.env) file and call variables 
        // for the database credentials
        $dotenv = Dotenv::createImmutable("../../");
        $dotenv->load();

        // Creating a mysqli object and using the variables from withing the .env file located 
        // in the root directory of the project 
        $mysqli = new \Mysqli($_ENV['DB_HOST'], $_ENV['DB_USER'], $_ENV['DB_PASS'], $_ENV['DB_NAME']);
        if($mysqli->connect_errno)
        {
            $msg  = "Connection to the Database failed: ";
            $msg .= $mysqli->connect_error;
            $msg .= " (" . $mysqli->connect_errno . ")";
            exit($msg);
        } 

    }

    public static function genv(): string 
    {
        $dotenv = Dotenv::createImmutable("../../");
        $dotenv->load();

        return $_ENV['DB_NAME'] . " " . $_ENV['DB_PASS'];
    }

    

}














