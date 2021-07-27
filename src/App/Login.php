<?php

declare(strict_types = 1);

namespace Fin\Narekaltro\App;

class Login
{





    // public static function loginAdmin($id = null, $url = null) {
    //     if(!empty($id)) {
    //         $url = !empty($url) ? $url : self::$dashboard_admin;
    //         $_SESSION[self::$login_admin] = $id;
    //         $_SESSION[self::$valid_login] = 1;
    //         self::regenerate();
    //         Helper::redirect_to($url);
    //     }
    // }

    public static function redirectTo(string $url): void 
    {

        if($url) {
            header("Location: {$url}");
            exit;
        }

    }

}