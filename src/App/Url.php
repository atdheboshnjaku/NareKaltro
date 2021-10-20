<?php

namespace Fin\Narekaltro\App;

class Url
{

    public static function getParam(string $par): string|null 
    {
        return isset($_GET[$par]) && $_GET[$par] != "" ? $_GET[$par] : null;
    }


}