<?php

declare(strict_types = 1);

namespace Fin\Narekaltro\App;

class User extends Database 
{

    protected $table   = "Users";
    private $table_2 = "User_Roles";

    public int $id;

    public function authenticate(string $email, string $password): bool
    {

        if(!empty($email) && !empty($password)) {

            $sql = "SELECT * FROM {$this->table} 
                    WHERE `email` = '" . $this->escape($email) . "'";
            $user = $this->fetchOne($sql);
            if($user) {
                if(password_verify($password, $user["password"])) {
                    $this->id = (int) $user['id'];
                    $_SESSION['username'] = $user['name'];
                    return true;
                } else {
                    return false;
                }
            }
            return false;

        }
        return false;

    }

    public function createUser(array $args = null, string $password = null): bool
    {

        if(!empty($args) && !empty($password)) {
            $this->prepareToInsert($args);
            if($this->insert($this->table)) {
                return true;
            }
            return false;
        }
        return false;

    }

    public function updateUser(array $args, string $id): bool 
    {

        if(!empty($args) && !empty($id)) {
            $this->prepareToUpdate($args);
            return $this->update($this->table, $id);
        }

    }

    public function getUserByEmail(string $email): array|null
    {
        if(!empty($email)) {
            $sql = "SELECT `id` FROM {$this->table} 
                    WHERE `email` = '" . $this->escape($email) . "'";
            return $this->fetchOne($sql);
        }

    }


    public function getUser(string $id): array
    {

        $sql = "SELECT * FROM {$this->table}
                 WHERE `id` =" . $this->escape($id);
        return $this->fetchOne($sql);

    }

    public function getUsers(): array
    {

        $sql = "SELECT * FROM {$this->table}
                WHERE `status` = 1";
        return $this->fetchAll($sql);

    }

    public function deleteUser(string $id): bool
    {

        if(!empty($id)) {
            if($this->deleteRecord($this->table, $id)) {
                return true;
            }    
        }

    }

    public function removeUser(string $id): bool 
    {

        if(!empty($id)) {
            if($this->deactivateUser($this->table, $id)) {
                return true;
            }
        }

    }

    public function userCount(): array 
    {

        $sql = "SELECT COUNT(*) FROM {$this->table} WHERE `status` = 1";
        return $this->fetchOne($sql);

    }

    public function employeeLocationCountById(string $id): array 
    {

        if(!empty($id)) {
            $sql = "SELECT COUNT(*) FROM {$this->table}
                    WHERE `location_id` = '". $this->escape($id) ."'
                    AND `status` = 1
                    AND `role_id` > 1 ";
        }
        return $this->fetchOne($sql);

    }

    public function clientLocationCountById(string $id): array 
    {

        if(!empty($id)) {
            $sql = "SELECT COUNT(*) FROM {$this->table}
                    WHERE `location_id` = '". $this->escape($id) ."'
                    AND `role_id` = 1
                    AND `status` = 1";
            return $this->fetchOne($sql);
        }

    }

    public function checkUserHasThisLocation(string $id): bool 
    {

        if(!empty($id)) {
            $sql = "SELECT `location_id` FROM {$this->table}
                    WHERE `location_id` = '". $this->escape($id) ."'
                    AND `status` = 1";
                $result = $this->fetchAll($sql);
                if($result) {
                    return true;
                }
        }
        return false;

    }

    public function getUserRoles(): array 
    {

        $sql = "SELECT * FROM {$this->table_2}";
        return $this->fetchAll($sql);

    }

}










