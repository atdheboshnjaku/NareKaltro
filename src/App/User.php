<?php

declare(strict_types = 1);

namespace Fin\Narekaltro\App;

class User extends Database 
{

    private $table = "Users";

    public string $id;

    public function authenticate(string $email, string $password): bool
    {

        if(!empty($email) && !empty($password)) {

            $sql = "SELECT * FROM {$this->table} 
                    WHERE `email` = '" . $this->escape($email) . "'";
            $user = $this->fetchOne($sql);
            if($user) {
                if(password_verify($password, $user['password'])) {
                    $this->id = $user['id'];
                    return true;
                } else {
                    return false;
                }
            }
            return false;

        }

    }

    // public function authenticate($email = null, $password = null) {
    //     if(!empty($email) && !empty($password)) {
    //         $email = $this->db->escape($email);
    //         $password = $this->db->escape($password);
    //         $query = "SELECT * FROM {$this->table} WHERE email = '".$email."'";
    //         $admin = $this->db->fetchOne($query);
    //         if($admin) {
    //             if(password_verify($password, $admin['password'])) {
    //                 $this->id = $admin['id'];
    //                 return true;
    //             } else {
    //                 return false;
    //             }
    //         }
    //         return false;
    //     }
    // }

    public function getUser(string $id): array
    {

        $sql = "SELECT * FROM {$this->table}
                 WHERE `id` ='" . $this->escape($id) . "'";
        return $this->fetchOne($sql);

    }

}