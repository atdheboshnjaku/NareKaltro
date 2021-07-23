<?php

declare(strict_types = 1);
namespace Fin\Narekaltro\App;

class Database
{

    private $server = "localhost";
    private $user   = "fin_narekaltro";
    private $pass   = "UGSCtT1Zmqm9FY1I";
    private $name   = "fin_narekaltro";

    //private $connection = false;

    public function __construct() 
    {
        $this->connect();
    }

    public function connect()  
    {

        $mysqli = new \Mysqli($this->server, $this->user, $this->pass, $this->name);
        if($mysqli->connect_errno)
        {
            $msg  = "Connection to Db failed: ";
            $msg .= $mysqli->connect_error;
            $msg .= " (" . $mysqli->connect_errno . ")";
            exit($msg);
        }

    }

    

}














