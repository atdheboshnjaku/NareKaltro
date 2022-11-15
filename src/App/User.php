<?php

declare(strict_types = 1);

namespace Fin\Narekaltro\App;

class User extends Database 
{

    protected $table   = "Users";
    private $table_2   = "User_Roles";

    public int $id;

    public function getColumnName(): array  
    {

        return $this->getTableColumnName($this->table);

    }

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

    public function createUser(array $args = null, ?string $password = null): bool
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

    public function registerUser(array $args = null): bool
    {

        if(!empty($args)) {
            $this->prepareToInsert($args);
            if($this->insert($this->table)) {
                if($this->sendEmail($args['email'], $args['hash'])) {
                    
                    return true;

                }
            }
            return false;
        }
        return false;

    }

    public function verifyUser(array|string $args, string $hash, ?string $password = null): bool 
    {

        if(!empty($args) && !empty($hash) && !empty($password)) {

            if($userID = $this->hashExists($hash)) {
                $id = $userID['id'];
                $args['status'] = "1";
                $this->prepareToUpdate($args);
                return $this->update($this->table, $id);

            }
        }

    }

    private function hashExists(string $hash): array|null
    {

        $sql = "SELECT `id`, `hash` FROM {$this->table}
                WHERE `hash` = '". $this->escape($hash) ."'";

            return $this->fetchOne($sql);

    }

    public function sendEmail(string $email, string $hash): bool 
    {

        $to       = $email;
        $subject  = 'Please verify your account';
        $message  = '
                    <html>
                    <head>
                        <title>Please verify email</title> 
                    </head>
                    <body>
                        <h1>Verify account by clicking on the link below</h1><br>
                        <p><a href="https://fin.narekaltro.com/verify?hash='. $hash  .'">Verify Email</a></p>
                        <p>If the link above does not work, please visit this url to verify: <br>
                        https://fin.narekaltro.com/verify?hash='.$hash.'
                        </p>
                    </body>
                    </html>';
        $headers  = 'MIME-Version: 1.0' . "\r\n" .
                    'Content-Type: text/html; charset=UTF-8' . "\r\n" .
                    'From: noreply@narekaltro.com'       . "\r\n" .
                    'Reply-To: noreply@narekaltro.com' . "\r\n" .
                    'X-Mailer: PHP/' . phpversion();

        if(mail($to, $subject, $message, $headers)) {
            return true;
        }

    }

    public function updateUser(array $args, string $id): bool 
    {

        if(!empty($args) && !empty($id)) {
            $this->prepareToUpdate($args);
            return $this->update($this->table, $id);
        }

    }

    public function getCreatedUserID(): int|string 
    {

        return $this->lastId();

    }

    public function getUserByEmail(string $email, string $id = null): array|null
    {
        if(!empty($email)) {
            $sql = "SELECT `id` FROM {$this->table} 
                    WHERE `email` = '" . $this->escape($email) . "' ";
                    if(!empty($id)) {
                        $sql .= "AND NOT `id` = '". $this->escape($id) ."'";
                    }
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
                WHERE `status` = 1
                AND `role_id` > 1";
        return $this->fetchAll($sql);

    }

    public function getUserLocationID(string $id): ?array 
    {

        $sql = "SELECT `location_id` FROM $this->table
                WHERE `id` = '" . $this->escape($id) . "'";
        return $this->fetchOne($sql);

    }

    public function getClients(): array
    {

        $sql = "SELECT * FROM {$this->table}
                WHERE `status` = 1
                AND `role_id` = 1";
        return $this->fetchAll($sql);

    }

    public function getClient(string $value): array 
    {

        $columnName = $this->getColumnName();
        $column = $columnName['COLUMN_NAME'];
        return $this->getRecordFromTableColumnValue($this->table, $column, $value);
        // $sql = "SELECT * FROM {$this->table}
        //         WHERE `".$this->escape($columnName['COLUMN_NAME'])."` = '". $this->escape($id) ."'";
        //         return $this->fetchOne($sql);

    }

    public function getClientCountryById(string $id): array  
    {

       $columnName = $this->getColumnName();
       $sql = "SELECT `country` FROM {$this->table}
               WHERE `{$this->escape($columnName['COLUMN_NAME'])}` = '". $this->escape($id) ."'";
               return $this->fetchOne($sql);

    }

    public function getClientInitials(string $fullname): string 
    {

        $safeFullname = [];
        $safeFullname[] = $this->escape($fullname);
        $initials = implode('', array_map(function($name) {
            preg_match_all('/\b\w/', $name, $matches);
            return implode('', $matches[0]);
        }, $safeFullname));
        return $initials; 

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

        $sql = "SELECT COUNT(*) FROM {$this->table} 
                WHERE `status` = 1
                AND `role_id` > 1";
        return $this->fetchOne($sql);

    }

    public function clientCount(): array 
    {

        $sql = "SELECT COUNT(*) FROM {$this->table}
                WHERE `status` = 1
                AND `role_id` = 1";
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

    public function getUserLevelName(string $id): array 
    {

        if(!empty($id)) {
            $sql = "SELECT `name` FROM {$this->table_2}
                    WHERE `level` = '". $this->escape($id) ."'";
                    return $this->fetchOne($sql);
        }

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

        $sql = "SELECT * FROM {$this->table_2}
                WHERE `level` > 1";
        return $this->fetchAll($sql);

    }

}










