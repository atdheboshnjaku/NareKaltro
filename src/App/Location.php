<?php

declare(strict_types = 1);

namespace Fin\Narekaltro\App;

class Location extends Database
{

    private $table = "Business_Locations";

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

    public function getBusinessLocations(): array|null
    {

        $sql = "SELECT * FROM {$this->table}";
        return $this->fetchAll($sql);

    }

    public function getLocationByEmail(string $name): array|null
    {

        if(!empty($name)) {
            $sql = "SELECT `name` FROM {$this->table} 
                WHERE `name` ='" . $this->escape($name) . "'";
            return $this->fetchOne($sql);
        }

    }

    public function locationCount(): array 
    {

        $sql = "SELECT COUNT(*) FROM {$this->table}";
        return $this->fetchOne($sql);

    }

}









