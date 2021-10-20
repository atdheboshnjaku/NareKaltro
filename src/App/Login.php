<?php

declare(strict_types = 1);

namespace Fin\Narekaltro\App;

class Login
{

    public static function redirectTo(string $url): void 
    {

        if($url) {
            header("Location: {$url}");
            exit;
        }

    }

    public static function passwordEncrypt(string $password): string
    {

        if(!empty($password)) {
            return password_hash($password, PASSWORD_BCRYPT, ["cost" => 12]);
        }

    }

    public static function getActive(array $page = null): string|null 
    {
        if(!empty($page)) {
            if(is_array($page)) {
                $error = array();
                foreach($page as $key => $value) {
                    if(Url::getParam($key) != $value) {
                        array_push($error, $key);
                    }
                }
                return empty($error) ? "active" : null;
            }
        }
        return $page == Url::cPage() ? "active" : null;
    }


}