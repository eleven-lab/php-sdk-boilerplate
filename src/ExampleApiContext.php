<?php namespace SDK;

use SDK\Base\ApiContext as BaseApiContext;

class ExampleApiContext extends BaseApiContext
{

    /**
     * @param array $config
     * @return Base\Credentials|ExampleCredentials
     */
    protected function buildCredentials(array $config)
    {

        $clientId = $config[$this->getMode()]['credentials']['client_id'];
        $secret = $config[$this->getMode()]['credentials']['secret'];

        return ExampleCredentials::make($clientId, $secret);

    }

}