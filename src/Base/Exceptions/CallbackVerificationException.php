<?php namespace SDK\Base\Exceptions;

/**
 * Class CallbackVerificationException
 * @package itTaxi\SDK\Exceptions
 */
class CallbackVerificationException extends BaseException
{
    protected $message = "Callback verification failed";

    /**
     * Class constructor.
     *
     * @param string $message
     */
    public function __construct($message = '')
    {
        $this->message .= ': ' . $message;
        parent::__construct($this->message, BaseException::CALLBACK_VERIFICATION_FAILED);
    }
}