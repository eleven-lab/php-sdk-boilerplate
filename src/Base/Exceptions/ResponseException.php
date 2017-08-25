<?php namespace SDK\Base\Exceptions;

class ResponseException extends BaseException
{
    protected $statusCode = 500;
    protected $response = null;

    public function __construct($httpStatusCode, $body, $exception = null)
    {
        $this->statusCode = $httpStatusCode;
        $this->response = $body;
        $message = "[HTTP {$this->statusCode}] {$this->response}";
        parent::__construct($message, BaseException::API_REQUEST_ERROR);
    }

    /**
     * Get the original response body
     * @return string|null
     */
    public function getResponseBody()
    {
        return $this->response;
    }

}