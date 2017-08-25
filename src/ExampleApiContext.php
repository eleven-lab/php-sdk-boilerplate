<?php namespace itTaxi\SDK;

use SDK\Base\ApiContext as BaseApiContext;

/**
 * Class ApiContext
 * @package itTaxi\API\Connection
 */
class ExampleApiContext extends BaseApiContext
{

    protected function buildCredentials(array $config)
    {

        $clientId = $config[$this->getMode()]['credentials']['client_id'];
        $secret = $config[$this->getMode()]['credentials']['secret'];

        return ExampleCredentials::make($clientId, $secret);

    }

}