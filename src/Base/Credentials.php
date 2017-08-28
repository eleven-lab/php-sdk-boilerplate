<?php

namespace SDK\Base;


class Credentials
{

    /**
     * @var string
     */
    protected $username;

    /**
     * @var string
     */
    protected $password;

    public function __construct($username, $password)
    {

        $this->username = $username;
        $this->password = $password;

    }

    public static function make($username, $password)
    {
        return new self($username, $password);
    }

    public function getUsername()
    {

        return $this->username;

    }

    public function getPassword()
    {

        return $this->password;

    }

    public function toArray()
    {

        return [
            $this->getUsername(),
            $this->getPassword()
        ];

    }

    public function toHttpBasic()
    {

        return base64_encode(join(':', $this->toArray()));

    }

}