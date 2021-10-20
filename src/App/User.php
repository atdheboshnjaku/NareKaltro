<?php

declare(strict_types = 1);

namespace Fin\Narekaltro\App;

class User extends Database 
{

    private $table = "Users";

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

        $sql = "SELECT * FROM {$this->table}";
        return $this->fetchAll($sql);

    }

    public function userCount(): array 
    {

        $sql = "SELECT COUNT(*) FROM {$this->table}";
        return $this->fetchOne($sql);

    }

}










