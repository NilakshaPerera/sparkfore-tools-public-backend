<?php

namespace App\Domain\DataClasses\Moodle;

class MoodleUpdateUserDto
{
    private $id;
    private $username;
    private $password;
    private $firstname;
    private $lastname;
    private $email;

    public function __construct($id, $username, $password, $firstname, $lastname, $email)
    {
        $this->id = $id;
        $this->username = $username;
        $this->password = $password;
        $this->firstname = $firstname;
        $this->lastname = $lastname;
        $this->email = $email;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getUsername()
    {
        return $this->username;
    }

    public function getPassword()
    {
        return $this->password;
    }

    public function getFirstname()
    {
        return $this->firstname;
    }

    public function getLastname()
    {
        return $this->lastname;
    }

    public function getEmail()
    {
        return $this->email;
    }


}
