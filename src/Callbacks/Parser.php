<?php

namespace ElevenLab\API\Boilerplate\Callbacks;


use Illuminate\Support\Arr;
use itTaxi\SDK\ApiContext;
use itTaxi\SDK\Exceptions\CallbackVerificationException;
use itTaxi\SDK\Exceptions\CannotParseCallbackException;
use itTaxi\SDK\Exceptions\UnknownCallbackType;

class Parser implements ParseCallbacks
{

    /**
     * @var ApiContext
     */
    protected $context;

    /**
     * Parser constructor.
     * @param ApiContext $context
     */
    public function __construct(ApiContext $context)
    {
        $this->context = $context;
    }

    /**
     * @param array $body
     * @param array $headers
     * @return mixed
     * @throws CannotParseCallbackException|CannotParseCallbackException|CallbackVerificationException|UnknownCallbackType
     */
    public function parse($body, $headers)
    {

        /*
         * This are common callback parameters, so if the callback does not have
         * one of this parameters it cannot be parsed
         */
        if(!Arr::has($body, ['event', 'data', 'created_at'])){
            throw new CannotParseCallbackException($body);
        }

        /*
         * Here we verify the callback signature with the application secret provided
         */
        if($this->context->verifyCallbacks()){
            if(!array_key_exists('X-Request-Verify', $headers)){
                throw new CallbackVerificationException('missing header X-Request-Verify');
            }
            $verifier = new Verifier();
            $plaintext = json_encode($body);
            $isValid = $verifier->verify($plaintext, $headers['X-Request-Verify'], $this->context->getSecret());
            if(!$isValid)
                throw new CallbackVerificationException('digest received ' . $headers['X-Request-Verify']);
        }

        /*
         * If the returned callback has an invalid event we will not accept it
         */
        $type = Arr::get($body, 'event');
        if(!Arr::exists(Callback::getTypes(), $type)){
            throw new UnknownCallbackType($type);
        }

        /*
         * Then we parse the callback
         */
        $class = Callback::getTypeClass($type);
        return $class::parse($type, $body['created_at'], $body['data']);

    }

    /**
     * @return ApiContext
     */
    public function getContext()
    {
        return $this->context;
    }
}