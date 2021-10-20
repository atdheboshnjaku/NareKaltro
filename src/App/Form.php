<?php

declare(strict_types = 1);

namespace Fin\Narekaltro\App;

class Form
{

    public function isPost(string $field = ""): bool
    {

        if(!empty($field)) {
            if(isset($_POST[$field])) {
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

    public function getPost(string $field): string|null
    {

        if(!empty($field)) {
            return $this->isPost($field) ? strip_tags($_POST[$field]) : null;
        }

    }

    public function getPostArray(array $expected = null): array
    {

        $out = [];
        if($this->isPost()) {
            foreach($_POST as $key => $value) {
                if(!empty($expected)) {
                    if(in_array($key, $expected)) {
                        $out[$key] = strip_tags($value);
                    }
                } else {
                    $out[$key] = strip_tags($value);
                }
            }
        }
        return $out;

    }


}







