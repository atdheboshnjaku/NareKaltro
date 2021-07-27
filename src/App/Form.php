<?php

declare(strict_types = 1);

namespace Fin\Narekaltro\App;

class Form
{

    public function isPost(string $input): bool 
    {

        if(!empty($input)) {
            if(isset($_POST[$input])) {
                return true;
            }
            return false;
        } else {
            if(!empty($_POST)) {
                return true;
            }
            return false;
        }

    }

    public function getPost(string $input): string|null {
        if(!empty($input)) {
            return $this->isPost($input) ? strip_tags($_POST[$input]) : null;
        }
    }

}







