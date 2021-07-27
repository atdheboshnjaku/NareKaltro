<?php

declare(strict_types = 1);

namespace Fin\Narekaltro\App;

use Dotenv\Dotenv;

class Database
{

    private $db = false;
    public $lastQuery = null;

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
        $this->db = new \Mysqli($_ENV['DB_HOST'], $_ENV['DB_USER'], $_ENV['DB_PASS'], $_ENV['DB_NAME']);
        if($this->db->connect_errno) {
            $msg  = "Connection to the Database failed: ";
            $msg .= $this->db->connect_error;
            $msg .= " (" . $this->db->connect_errno . ")";
            exit($msg);
        } 

    }

    public function closeConnection(): void  
    {

        if(isset($this->db)) {
            $this->db->close();
        }

    }

    public function escape(string $string): string 
    {

        $escape_string = $this->db->escape_string($string);
        return $escape_string;

    }

    public function query(string $query): array
    {

        $this->lastQuery = $query;
        $result = $this->db->query($query);
        $this->confirmQuery($result);
        return $result;

    }

    public function confirmQuery(string $result): string|int
    {

        if(!$result) {
            $output  = "Database query failed\n ";
            $output .= "Last SQL query: ". $this->lastQuery;
            die($output);
        } else {
            $this->db->affected_rows();
        }

    }

    public function fetchAll(string $query): array
    {

        $result = $this->db->query($query);
        $output = [];
        while($row = $result->fetch_assoc()) {
            $output[] = $row;
        }
        $result->free();
        return $output;

    }

    public function fetchOne(string $query): array
    {

        $result = $this->fetchAll($query);
        return array_shift($result);

    }
    
    public function lastId(): int|string
    {

        return $this->db->insert_id;

    }


    // Testing Dotenv
    public static function genv(): string 
    {
        $dotenv = Dotenv::createImmutable("../../");
        $dotenv->load();

        return $_ENV['DB_NAME'] . " " . $_ENV['DB_PASS'];
    }

    

}














