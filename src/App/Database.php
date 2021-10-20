<?php

declare(strict_types = 1);

namespace Fin\Narekaltro\App;

use Dotenv\Dotenv;

class Database
{

    private $db = false;

    public ?string $lastQuery = null;
    public array  $insertKeys = [];
    public array  $insertValues = [];
    
    public int $id;

    public function __construct() 
    {
        // Initializing a connection to the Database
        $this->connect();
    
    }

    public function connect()  
    {
        // Using vlucas/phpdotenv package to load the environment (.env) file and call variables 
        // for the database credentials
        $dotenv = Dotenv::createImmutable($_SERVER['DOCUMENT_ROOT']);
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

    public function escape(string|int $value): string|int
    {

        $escape_string = $this->db->escape_string($value);
        return $escape_string;

    }

    public function query(string $query): bool
    {

        $this->lastQuery = $query;
        $result = $this->db->query($query);
        $this->confirmQuery($result);
        return $result;

    }

    public function confirmQuery(bool $result): void
    {

        if(!$result) {
            $output  = "Database query failed\n ";
            $output .= "Last SQL query: ". $this->lastQuery;
            die($output);
        } else {
            $this->db->affected_rows;
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

    public function fetchOne(string $query): array|null
    {

        $result = $this->fetchAll($query);
        return array_shift($result);

    }
    
    public function lastId(): int
    {

        return $this->db->insert_id;

    }

    public function prepareToInsert(array $args = null): void
    {

        if(!empty($args)) {

            foreach($args as $key => $value) {
                $this->insertKeys[] = $key;
                $this->insertValues[] = $this->escape($value);
            }

        }

    }

    public function insert(string $table = null): bool
    {

        if(
            !empty($table) && 
            !empty($this->insertKeys) && 
            !empty($this->insertValues)
        ) {

            $sql  = "INSERT INTO `{$table}` (`";
            $sql .= implode("`, `", $this->insertKeys);
            $sql .= "`) VALUES ('";
            $sql .= implode("', '", $this->insertValues);
            $sql .= "')";

            if($this->query($sql)) {
                $this->id = $this->lastId();
                return true;
            }
            return false;

        }

    }


    // Testing Dotenv
    public static function genv(): string 
    {
        $dotenv = Dotenv::createImmutable("../../");
        $dotenv->load();

        return $_ENV['DB_NAME'] . " " . $_ENV['DB_PASS'];
    }

    

}














