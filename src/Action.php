<?php namespace ElevenLab\API\Boilerplate;

use Carbon\Carbon;
use Illuminate\Contracts\Cache\Repository;
use GuzzleHttp\Psr7\Request as Psr7Request;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Arr;
use Illuminate\Translation\Translator;
use Illuminate\Validation\Validator;
use itTaxi\SDK\Exceptions\ArgumentsException;
use Illuminate\Validation\Factory as ValidationFactory;
use Illuminate\Translation\FileLoader;
use itTaxi\SDK\Exceptions\ResponseException;
use itTaxi\SDK\Exceptions\UrlParametersException;
use itTaxi\SDK\Objects\Error;

/**
 * Class Action
 *
 * The rationale behind that class is:
 * - here we implement all the behaviors needed for request building and handling
 * - that class must be implemented from a APIEndpoint class, that must give all the common configurations for all the endpoints
 * - each endpoint must extend APIEndpoint and define its specific information like method, endpoint,
 *
 * @package itTaxi\SDK\Base
 */
abstract class Action implements FakeableAction
{
    /**
     * @var Repository
     */
    protected $cache;

    /**
     * @var ApiContext
     */
    protected $context;

    /**
     * Array that holds all the parameters that will be inserted into the request body, according to the payload type.
     * @var array
     */
    protected $request_params = [];

    /**
     * Array that holds the url parameters that must be specified to get a valid endpoint
     */
    protected $url_params = [];

    /**
     * Original response
     * @var Response
     */
    protected $response = null;


    /**
     * Check whether the action has a response
     *
     * @return bool
     */
    public function hasResponse()
    {
        return !is_null($this->response);
    }

    /**
     * Returns the original response
     *
     * @return Response
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * Leaf classes must override that attribute with the relative endpoint url.
     * @return string
     */
    protected abstract function getEndpoint();

    /**
     * Leaf classes must override that attribute with the http method.
     * @return string
     */
    protected abstract function getMethod();

    /**
     * Leaf classes must override that attribute with the needed base headers.
     * @return array
     */
    protected abstract function getHeaders();

    /**
     * Leaf classes must override that attribute with the validation rules for the request parameters
     * @return array
     */
    protected abstract function getRequestParamsRules();

    /**
     * Leaf classes must override that attribute with the validation rules for the response parameters
     * @return array
     */
    protected abstract function getResponseParamsRules();

    /**
     * Leaf classes must override this function with the validation rules for the request endpoint
     */
    protected abstract function getUrlParametersRules();

    /**
     * Supported payload types:
     * - raw (http://docs.guzzlephp.org/en/latest/request-options.html#body):
     * - form_params (http://docs.guzzlephp.org/en/latest/request-options.html#form_params):
     * - multipart (http://docs.guzzlephp.org/en/latest/request-options.html#multipart)
     * - json (http://docs.guzzlephp.org/en/latest/request-options.html#json)
     *
     * @return string
     */
    protected abstract function getPayloadType();

    /**
     * Parse the response, should be useful to instantiate an object.
     * @param $response
     * @return mixed
     */
    protected abstract function parseResponse(array $response);

    /**
     * BaseEndpoint constructor.
     * @param ApiContext $context
     * @param Repository $cache
     */
    public function __construct(ApiContext $context, Repository $cache)
    {
        $this->context = $context;
        $this->cache = $cache;
    }

    public static function fake($context, $cache, $responseStatusCode, $responseJsonBody){

        $mock = \Mockery::mock(static::class, [$context, $cache])->makePartial();

        $mock->shouldAllowMockingProtectedMethods()
            ->shouldReceive('makeRequest')
            ->andReturn($mock->makeRequestFake($responseStatusCode, $responseJsonBody));

        $mock->shouldReceive('authenticate')
            ->andReturnUsing(function(Psr7Request $request) {
                return $request->withHeader('Authorization', 'Bearer ' . str_random(32));
            });

        return $mock;
    }

    /**
     *
     * Base request parameter getter
     * @param string $attribute
     * @param mixed $default
     *
     * @return mixed
     */
    public function getRequestParameter($attribute, $default = null)
    {
        return Arr::get($this->request_params, $attribute, $default);
    }

    /**
     * Base url parameter getter
     * @param string $key
     * @param mixed $default
     *
     * @return mixed
     */
    public function getUrlParameter($key, $default = null)
    {
        return Arr::get($this->url_params, $key, $default);
    }

    /**
     * @param int|Carbon $timestamp
     * @return int
     */
    public function parseTimestamp($timestamp)
    {
        if($timestamp instanceof Carbon)
        {
            return $timestamp->timestamp;
        }

        return $timestamp;
    }

    /**
     * Take a request and add authentication data
     * @param Psr7Request $request
     * @return Psr7Request $request
     */
    protected function authenticate(Psr7Request $request)
    {
        return $request;
    }

    /**
     * Flushed cached authentication credentials if
     * present
     *
     * @return mixed
     */
    abstract protected function flushAuthentication();

    protected function getHttpAuthCredentials()
    {
        return [];
    }

    /**
     * @return callable
     */
    private function getAuthenticateCallable()
    {
        return function(Psr7Request $request) { return $this->authenticate($request); };
    }

    /**
     * @param array $params
     * @param array $rules
     *
     * @return Validator
     */
    private function makeValidator(array $params, array $rules)
    {
        if(!empty($rules)) {
            $files = new Filesystem();
            $loader = new FileLoader($files, $this->context->getLangResource());
            $translator = new Translator($loader, $this->context->getLangLocale());
            $factory = new ValidationFactory($translator);
            $validator = $factory->make($params, $rules);

            return $validator;
        }

        return null;
    }

    /**
     * @param array $params
     * @param array $rules
     * @throws UrlParametersException
     */
    private function validateUrlParameters(array $params, array $rules)
    {
        $validator = $this->makeValidator($params, $rules);
        if ($validator && $validator->fails())
            throw new UrlParametersException($validator->getMessageBag()->all());
    }

    /**
     * @param array $params
     * @param array $rules
     * @throws ArgumentsException
     */
    private function validateBodyParameters(array $params, array $rules)
    {

        $validator = $this->makeValidator($params, $rules);
        if($validator && $validator->fails())
            throw new ArgumentsException($validator->getMessageBag()->all());

    }

    /**
     * That function must return an array, to be validated against response_params_rules.
     * TODO: add missing content types
     *
     * @param $mimetype
     * @param $body
     * @throws \Exception
     * @return array
     */
    private function parseRawBody($mimetype, $body)
    {
        switch($mimetype){
            case 'application/json':
            case 'application/x-javascript':
            case 'text/x-json':
            case 'text/javascript':
            case 'text/x-javascript':
                return json_decode($body, true);
            case 'text/html':
                return null;

            default:
                throw new \Exception("Wrong body content type: $mimetype");
        }
    }

    protected function buildPayload()
    {
        return $this->request_params;
    }

    /**
     * @return array
     * @throws ResponseException
     */
    public function send()
    {
        $this->validateUrlParameters($this->url_params, $this->getUrlParametersRules());
        $this->validateBodyParameters($this->request_params, $this->getRequestParamsRules());

        try {
            $this->response = $this->makeRequest()->run();
        }catch(ResponseException $exception) {
            /*
             * If the token has expired we fetch the token one more time and we execute the action again
             */
            if($exception->getApiError()->error_code === Error::EXPIRED_TOKEN){
                $this->flushAuthentication();
                return $this->send();
            }else {
                throw $exception;
            }
        }

        if(!$this->response->isEmpty()) {
            $parsed_response = $this->parseRawBody(
                $this->response->getRawResponse()->getHeaderLine('Content-Type'),
                $this->response->getRawBody()
            );
        }else{
            $parsed_response = [];
        }

        $this->validateBodyParameters($parsed_response, $this->getResponseParamsRules());

        return $this->parseResponse($parsed_response);
    }

    /**
     * Prepare the request
     * @return Request
     */
    protected function makeRequest()
    {
        return (new Request(
            $this->context->getBaseUrl(),
            $this->context->getTimeout(),
            $this->getPayloadType(),
            $this->getMethod(),
            $this->getEndpoint(),
            $this->getHeaders(),
            $this->buildPayload(),
            $this->getAuthenticateCallable(),
            $this->getHttpAuthCredentials()
        ));
    }

    /**
     * Prepare the fake request
     * @param int $statusCode
     * @param string $responseBody
     *
     * @return Request
     */
    protected function makeRequestFake($statusCode, $responseBody){

        return Request::fake(
            $statusCode,
            $responseBody,
            $this->context->getBaseUrl(),
            $this->context->getTimeout(),
            $this->getPayloadType(),
            $this->getMethod(),
            $this->getEndpoint(),
            $this->getHeaders(),
            $this->buildPayload(),
            $this->getAuthenticateCallable(),
            $this->getHttpAuthCredentials()
        );

    }
}