<?php namespace SDK\Base\Exceptions;

/**
 * Class CannotParseCallbackException
 * @package itTaxi\SDK\Exceptions
 */
class UnknownCallbackType extends BaseException
{
    protected $message = "Cannot parse callback: unknown type";

    /**
     * Class constructor.
     * @param string $type
     */
    public function __construct($type)
    {
        $message = $this->message . ' \'' . $type . '\'';
        parent::__construct($message, BaseException::UNKNOWN_CALLBACK_TYPE);
    }
}