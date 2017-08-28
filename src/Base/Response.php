<?php

namespace SDK\Base;

use GuzzleHttp\Psr7\Response as Psr7Response;
use SDK\Base\Exceptions\ResponseException;

class Response
{
    /**
     * Unique identifier of the current request
     * @var int
     */
    private $uuid;

    /**
     * Request generating this response
     * @var Request
     */
    protected $request;

    /**
     * Original http response
     * @var Psr7Response
     */
    protected $psr7_response;

    /**
     * @var int
     */
    private $execution_time;


    /**
     * APIResponse constructor.
     * @param Request $request
     * @param Psr7Response $psr7_response
     * @param integer $execution_time
     * @throws ResponseException
     */
    public function __construct(Request $request, Psr7Response $psr7_response, $execution_time)
    {
        $this->uuid = rand();
        $this->request = $request;
        $this->psr7_response = $psr7_response;
        $this->execution_time = $execution_time;
    }

    /**
     * @return bool
     */
    public function isEmpty()
    {
        return ($this->getRawResponse()->getStatusCode() === 204
        || $this->getRawResponse()->getStatusCode() === 304);
    }

    /**
     * @return int
     */
    public function getUUID()
    {
        return $this->uuid;
    }

    /**
     * @return Request
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * @return Psr7Response
     */
    public function getRawResponse()
    {
        return $this->psr7_response;
    }

    /**
     * @return int
     */
    public function getExecutionTime()
    {
        return $this->execution_time;
    }

    /**
     *
     * @param bool $rewind
     *
     * @return string
     */
    public function getRawBody($rewind = false)
    {
        if($rewind) {
            $this->psr7_response->getBody()->rewind();
        }
        return $this->psr7_response->getBody()->getContents();
    }
}
