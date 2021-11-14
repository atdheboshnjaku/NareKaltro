<?php

declare(strict_types = 1);

namespace Fin\Narekaltro\App;

class Location extends Database
{

    protected $table = "Business_Locations";

    //public int $id;

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

    public function getBusinessLocations(): array|null
    {

        $sql = "SELECT * FROM {$this->table}";
        return $this->fetchAll($sql);

    }

    public function getLocationByName(string $name): array|null
    {

        if(!empty($name)) {
            $sql = "SELECT `name` FROM {$this->table} 
                    WHERE `name` = '" . $this->escape($name) . "'";
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
            if($this->deleteRecord($this->table, $id)) {
                return true;
            }
        }

    }

    public function locationCount(): array 
    {

        $sql = "SELECT COUNT(*) FROM {$this->table}";
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









