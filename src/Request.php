<?php namespace ElevenLab\API\Boilerplate;

use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Handler\CurlHandler;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\Psr7\Response as GuzzleResponse;
use itTaxi\SDK\Exceptions\ArgumentsException;
use Psr\Http\Message\RequestInterface;
use itTaxi\SDK\Exceptions\BaseException;
use itTaxi\SDK\Exceptions\ResponseException;
use itTaxi\SDK\Exceptions\ConnectionException;

/**
 * Class Request
 * @package itTaxi\SDK\Base
 */
class Request implements FakeableRequest
{
    /**
     * @var Request
     */
    private $client;

    /**
     * @var string
     */
    private $base_url;

    /**
     * @var int
     */
    private $timeout;

    /**
     * @var string
     */
    private $payload_type;

    /**
     * @var callable
     */
    private $authenticate;

    /**
     * @var string
     */
    private $method;

    /**
     * @var string
     */
    private $endpoint;

    /**
     * @var array
     */
    private $headers = [];

    /**
     * @var array
     */
    private $auth = [];

    /**
     * Request constructor.
     * @param $base_url
     * @param $timeout
     * @param $payload_type
     * @param $method
     * @param $endpoint
     * @param array $headers
     * @param $payload
     * @param callable $authenticate
     * @param array $auth
     */
    public function __construct($base_url, $timeout, $payload_type, $method, $endpoint, array $headers, $payload, callable $authenticate, $auth = [])
    {
        $this->base_url = $base_url;
        $this->timeout = $timeout;
        $this->payload_type = $payload_type;
        $this->method = $method;
        $this->endpoint = $endpoint;
        $this->headers = $headers;
        $this->payload = $payload;
        $this->authenticate = $authenticate;
        $this->auth = $auth;
    }

    /**
     * That method is the middleware used by guzzle to add authentication to the request.
     *
     * @return \Closure
     * @param callable $authentication_method
     * @throws BaseException
     */
    private function add_authentication(callable $authentication_method)
    {
        return function (callable $handler) use ($authentication_method){
            return function (RequestInterface $request, array $options) use ($handler, $authentication_method) {
                $request = $authentication_method($request);
                return $handler($request, $options);
            };
        };
    }

    /**
     * @return array
     */
    private function buildHeaders()
    {
        return $this->headers;
    }

    private function buildAuth(array &$options)
    {
        if(!empty($this->auth)){
            $credentials = [ $this->auth['username'], $this->auth['password']];
            if(array_key_exists('digest', $this->auth)) {
                $credentials[] = $this->auth['digest'];
            }
            $options['auth'] = $credentials;
        }
    }

    /**
     * @param array $options
     * @throws BaseException
     */
    private function buildBody(array &$options)
    {
        if (empty($this->payload))
            return;

        switch ($this->payload_type){
            case 'json':
            case 'raw':
            case 'form_params':
            case 'multipart':
                $options[$this->payload_type] = $this->payload;
                break;

            default:
                throw new BaseException('Unknown payload type');
        }
    }

    /**
     * Builds the client needed to send the request.
     * @return GuzzleClient
     */
    protected function buildClient()
    {
        $stack = new HandlerStack();
        $stack->setHandler(new CurlHandler());
        $stack->push($this->add_authentication($this->authenticate));

        $options = [
            'timeout'  => $this->timeout,
            'base_uri' => $this->base_url,
            'headers' => $this->buildHeaders(),
            'handler' => $stack,
            'http_errors' => false
        ];
        $this->buildBody($options);
        $this->buildAuth($options);

        return new GuzzleClient($options);
    }

    /**
     * @return Response
     * @throws BaseException
     */
    public function run()
    {
        $this->client = $this->buildClient();

        $start_time = round(microtime(true) * 1000);

        try {
            $response = $this->client->request($this->method, $this->endpoint);
            $this->checkStatus($response);

        } catch (\Exception $e) {
            if(!$e instanceof BaseException) {
                throw new BaseException("Unknown exception", BaseException::UNKNOWN_ERROR, $e);
            }

            throw $e;
        }

        $end_time = round(microtime(true) * 1000);

        return new Response($this, $response, $end_time - $start_time);
    }

    /**
     *
     * Check if the response is successful
     *
     * @param GuzzleResponse $response
     * @throws ConnectionException
     * @throws ResponseException
     */
    private function checkStatus(GuzzleResponse $response)
    {
        $status_code = $response->getStatusCode();
        $body = $response->getBody()->getContents();
        $response->getBody()->rewind();
        if($status_code >= 400 && $status_code < 500){
            throw new ResponseException($status_code, $body);
        }else if($status_code >= 500){
            throw new ConnectionException($status_code, $body);
        }
    }


    /**
     *
     * Creates a mock of the request object for testing purposes
     *
     * @param $statusCode
     * @param $fakeBody
     * @param $base_url
     * @param $timeout
     * @param $payload_type
     * @param $method
     * @param $endpoint
     * @param array $headers
     * @param $payload
     * @param callable $authenticate
     * @param array $auth
     * @return \Mockery\Mock
     */
    public static function fake(
        $statusCode, $fakeBody,
        $base_url, $timeout, $payload_type,
        $method, $endpoint, array $headers,
        $payload, callable $authenticate, $auth = []
    )
    {

        $requestMock = \Mockery::mock(self::class , [
            $base_url, $timeout, $payload_type,
            $method, $endpoint, $headers, $payload,
            $authenticate, $auth
        ])->makePartial();

        $requestMock->shouldAllowMockingProtectedMethods()
            ->shouldReceive('buildClient')
            ->andReturnUsing(function() use($requestMock, $statusCode, $fakeBody){

                $headers = [
                    'Content-Type' => 'application/json',
                    'X-Api-Version' => 'v1.1'
                ];

                $mock = new MockHandler([
                    new GuzzleResponse($statusCode, $headers, $fakeBody)
                ]);

                $stack = HandlerStack::create($mock);
                $options = [
                    'timeout' => $requestMock->timeout,
                    'base_uri' => $requestMock->base_url,
                    'headers' => $requestMock->buildHeaders(),
                    'handler' => $stack,
                    'http_errors' => false
                ];
                $requestMock->buildBody($options);
                $requestMock->buildAuth($options);

                return new GuzzleClient($options);
            });

        return $requestMock->makePartial();
    }
}