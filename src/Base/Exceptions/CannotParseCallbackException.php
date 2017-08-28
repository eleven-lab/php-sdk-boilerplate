<?php namespace SDK\Base\Exceptions;

class CannotParseCallbackException extends BaseException
{
    protected $message = "Cannot parse callback: unknown format";

    /**
     * Class constructor.
     * @param array $payload
     */
    public function __construct(array $payload)
    {
        $message = $this->message . PHP_EOL . json_encode($payload);
        parent::__construct($message, BaseException::CANNOT_PARSE_CALLBACK);
    }
}