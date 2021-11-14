<?php

declare(strict_types = 1);

namespace Fin\Narekaltro\App;

class Validation 
{


    private array $error = [];

    public array $message = [
        "login"           => "Email/Password combination is not correct!",
        "email"           => "Please enter a valid email address",
        "name"            => "Please enter the users full name",
        "password"        => "Please enter your password",
        "user_exists"     => "This user email exists in the Database",
        "location_exists" => "This location already exists!",
        "role_id"         => "Please select a user role",
        "location_id"     => "Please select a user location"
    ];

    public array $expected = [];

    public array $required = [];

    public array $post = [];

    private object $objForm;

    public array $postRemove = [];

    public array $postFormat = [];

    public array $special = []; 

    public function __construct(Form $form)
    {

        $this->objForm = $form;

    }

    public function process()
    {

        if($this->objForm->isPost() && !empty($this->required)) {

            $this->post = $this->objForm->getPostArray($this->expected);
            if(!empty($this->post)) {
                foreach($this->post as $key => $value) {
                    $this->check($key, $value);
                }
            }

        }

    }

    public function check($key, $value)
    {

        if(!empty($this->special) && array_key_exists($key, $this->special)) {
            $this->checkSpecial($key, $value);
        } else {
            if(in_array($key, $this->required) && empty($value)) {
                $this->addToErrors($key);
            }
        }

    }

    public function checkSpecial($key, $value)
    {

        switch($this->special[$key]) {
            case 'email':
            if(!$this->isEmail($value)) {
                $this->addToErrors($key);
            }
            break;
        }

    }

    public function isEmail($email)
    {

        if(!empty($email)) {
            $result = filter_var($email, FILTER_VALIDATE_EMAIL);
            return !$result ? false : true;
        }
        return false;

    }

    public function isValid()
    {

        $this->process();
        if(empty($this->errors) && !empty($this->post)) {
            // remove unwanted fields
            if(!empty($this->postRemove)) {
                foreach($this->postRemove as $value) {
                    unset($this->post[$value]);
                }
            }
            // format required fields
            if(!empty($this->postFormat)) {
                foreach($this->postFormat as $key => $value) {
                    $this->format($key, $value);
                }
            }
            return true;
        }
        return false;

    }

    public function format(string $key, string $value): void
    {

        switch($value) {
            case 'password':
            $this->post[$key] = Login::passwordEncrypt($this->post[$key]);
            break;
        }

    }


    public function addToErrors($key)
    {

        $this->errors[] = $key;

    }

    public function validate($key)
    {

        if(!empty($this->errors) && in_array($key, $this->errors)) {
            return $this->wrapWarn($this->message[$key]);
        }

    }

    public function wrapWarn($mess)
    {

        if(!empty($mess)) {
            return "<span class=\"warn\" >{$mess}</span>";
        }

    }
    
    
    
    
    
    

    

}








