<?php 

use Fin\Narekaltro\App\Test;
use Fin\Narekaltro\App\Database;

require_once("../../vendor/autoload.php");


$db = new Database();
echo $db::genv();