<?php

declare(strict_types = 1);

namespace Fin\Narekaltro\App;

class Session
{

    private string $userId;

    public function __construct()
    {

        session_start();
        $this->checkLogin();

    }

    public function login($user): bool
    {

        if($user) {

            // Regenerating the session id after login to prevent session fixation
            session_regenerate_id();
            $_SESSION['userId'] = $user->id;
            $this->userId = $user->id;            

        }

        return true;

    }

    public function isLogged(): bool 
    {

        return isset($this->userId);

    }

    public function logout(): bool 
    {

        unset($_SESSION['userId']);
        unset($this->userId);
        return true;

    }

    private function checkLogin(): void
    {

        if(isset($_SESSION['userId'])) {
            $this->userId = $_SESSION['userId'];
        }

    }

}















