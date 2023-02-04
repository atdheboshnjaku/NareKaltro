<?php

declare(strict_types = 1);

namespace Fin\Narekaltro\App;

class Session extends Database
{

    public $userId;
    private $table   = "User_Tokens";
    private $table_2   = "Users";

    public function __construct()
    {
        
        if(session_status() === PHP_SESSION_NONE)
        {
            session_start();
        }

        parent::__construct();
    
        $this->checkLogin();

    }

    // public function login($user = null): array|bool|int
    // {

    //     if($user) {

    //         // Regenerating the session id after login to prevent session fixation
    //         session_regenerate_id();
    //         $_SESSION['userId'] = (int) $user->id;
    //         $this->userId = $user->id;        

    //     }

    //     return true;

    // }

    public function login($user = null): bool
    {

        if($user) {
            session_regenerate_id();
            if(is_array($user)) {
                // echo "<pre>";
                // var_dump($user);
                // echo "</pre>";
                $this->userId = $user['id']; 
                $_SESSION['username'] = $user['name']; 
                $_SESSION['userId'] = $this->userId;
            }

            if(is_object($user)) {
                return true;
            }
            
                    
            // return true;
        }

        return true;

    }

    public function isLogged(): bool|string 
    {

        //return isset($this->userId);
        if(isset($_SESSION['userId'])) {
            return true;
        }

        $token = filter_input(INPUT_COOKIE, 'remember_me', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        if($token && $this->tokenIsValid($token)) {
            $user = $this->getUserByToken($token);
            
            if($user) {
                return $this->login($user);
            } 
        }

        return false;

    }

    public function tokenIsValid(string $token): bool 
    {

        [$selector, $validator] = $this->parseToken($token);
        $tokens = $this->getUserTokenBySelector($selector);
        if(!$tokens) {
            return false;
        }

        return password_verify($validator, $tokens['hashed_validator']);

    }

    public function parseToken(string $token): ?array 
    {

        $parts = explode(':', $token);

        if($parts && count($parts) == 2) {
            return [$parts[0], $parts[1]];
        }

        return null;

    }

    public function getUserTokenBySelector(string $selector): ?array 
    {

        $sql = "SELECT * FROM $this->table 
                WHERE `selector` = '". $this->escape($selector) ."'
                AND `expiry` >= NOW()
                LIMIT 1";

            return $this->fetchOne($sql);

    }

    public function getUserByToken(string $token): ?array
    {

        try {
        $tokens = $this->parseToken($token);
        if(!$tokens) {
            return null;
        }

        $sql = "SELECT Users.id, name
                FROM $this->table_2
                INNER JOIN $this->table ON user_id = Users.id
                WHERE selector = '". $this->escape($tokens[0]) ."'
                AND expiry > NOW()
                LIMIT 1";

            return $this->fetchOne($sql);
        } catch (\Exception $e) {
            return $e->getMessage();
        }

    }

    public function logout(): bool 
    {

        if($this->isLogged()) {

            //$this->deleteUserToken($this->userId);
            unset($_SESSION['userId'], $_SESSION['username']);
            unset($this->userId);

            if(isset($_COOKIE['remember_me'])) {
                unset($_COOKIE['remember_me']);
                //setcookie('remember_me', null, -1);
                setcookie("remember_me", "", time() - 3600);
            }

            session_destroy();

            return true;

        }

    }

    private function checkLogin(): void
    {

        if(isset($_SESSION['userId'])) {
            $this->userId = $_SESSION['userId'];
        } 

    }

    public function getUserId(): string|int 
    {

        if(isset($_SESSION['userId'])) {
            return $this->userId = $_SESSION['userId'];
        }

    }
    //testing
    public function getU(string $id): ?array
    {

        $sql = "SELECT * FROM {$this->table_2}
                 WHERE `id` =" . $this->escape($id);
        return $this->fetchOne($sql);

    }

}