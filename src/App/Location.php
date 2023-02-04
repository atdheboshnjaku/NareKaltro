<?php

//declare(strict_types = 1);

namespace Fin\Narekaltro\App;

class Location extends Database
{

    protected $table = "Business_Locations";
    private $table_2 = "Countries";
    private $table_3 = "States";
    private $table_4 = "Cities";

    //public int $id;

    public function getColumnName(): array  
    {

        return $this->getTableColumnName($this->table);

    }

    public function createLocation(array $args): bool
    {

        if(!empty($args)) {
            $this->prepareToInsert($args);
            if($this->insert($this->table)) {
                return true;
            }
            return false;
        }
        return false;

    }

    public function updateLocation(array $args, string $id): bool 
    {

        if(!empty($args) && !empty($id)) {
            $this->prepareToUpdate($args);
            return $this->update($this->table, $id);
        }

    }

    public function getBusinessLocations(string $accountID): array|null
    {

        $sql = "SELECT * FROM {$this->table}
                WHERE `account_id` = '". $this->escape($accountID) ."'
                AND `status` = 1";
        return $this->fetchAll($sql);

    }

    public function getCountries(): array 
    {
        
        return $this->getAllCountries($this->table_2);

    }

    public function getStates(string $id): ?string
    {

        $sql = "SELECT `id`, `name` FROM {$this->table_3}
                WHERE `country_id` = '". $this->escape($id) ."'
                ORDER BY `name` ASC";
                $result = $this->fetchAll($sql);
                return json_encode($result);

    }

    public function getCities(string $state, string $country): bool|string
    {

        $sql = "SELECT `id`, `name` FROM {$this->table_4}
                WHERE `state_id` = '". $this->escape($state) ."'
                AND `country_id` = '". $this->escape($country) ."'
                ORDER BY `name` ASC";
                $result = $this->fetchAll($sql);
                return json_encode($result);

    }

    public function getLocationByName(string $name, string $accountID): array|null
    {

        if(!empty($name)) {
            $sql = "SELECT `name` FROM {$this->table} 
                    WHERE `name` = '" . $this->escape($name) . "'
                    AND `account_id` = '". $accountID ."'
                    AND `status` = 1";
            return $this->fetchOne($sql);
        }

    }

    public function getLocationById(string $id)
    {

        if(!empty($id)) {
            $sql = "SELECT `name` FROM {$this->table} WHERE `id` = '". $this->escape($id) ."'";
            return $this->fetchOne($sql);
        }

    }

    public function deleteLocation(string $id): bool 
    {

        if(!empty($id)) {
            if($this->deactivateLocation($this->table, $id)) {
                return true;
            }
        }

    }

    public function locationCount(string $accountID): array 
    {

        $sql = "SELECT COUNT(*) FROM {$this->table}
                WHERE `account_id` = '". $this->escape($accountID) ."'
                AND `status` = 1";
        return $this->fetchOne($sql);

    }

    public function locationCountById(string $id): array 
    {

        if(!empty($id)) {
            $result = $this->totalCountById($this->table, $id);
            return $result;
        }

    }

}









