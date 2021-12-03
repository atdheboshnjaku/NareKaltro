<?php

declare(strict_types = 1);

namespace Fin\Narekaltro\App\Traits;

trait Users
{


    private $user_table = "Users";

    public function getUser(string $id): array
    {

        $sql = "SELECT `name` FROM {$this->user_table}
                WHERE `id` = '". $this->escape($id) ."'";
                return $this->fetchOne($sql);

    }


}