<?php

namespace SDK;


use SDK\Base\Credentials;

class ExampleCredentials extends Credentials
{

    /**
     * @var string
     */
    protected $clientId;

    /**
     * @var string
     */
    protected $secret;

    public function __construct($clientId, $secret)
    {
        $this->clientId = $clientId;
        $this->secret = $secret;
        parent::__construct($clientId, $secret);
    }

    public static function make($clientId, $secret)
    {

        return new self($clientId, $secret);
    }

    public function getClientId()
    {

        return $this->getUsername();

    }

    public function getSecret()
    {

        return $this->getPassword();

    }

}