<?php

namespace SDK\Base\Exceptions;

/**
 * Class APIConnectionException
 * @package itTaxi\Exceptions
 */
class ConnectionException extends BaseException
{
    protected $statusCode;
    protected $response;

    /**
     * ConnectionException constructor.
     *
     * @param int $httpStatusCode
     * @param $payload
     * @param \Exception $exception
     */
    public function __construct($httpStatusCode, $payload, $exception = null)
    {
        $this->statusCode = $httpStatusCode;
        $this->response = $payload;
        $message = "[HTTP {$this->statusCode}] " . $this->response;

        parent::__construct($message, BaseException::CONNECTION_ERROR);
    }
}