<?php namespace SDK\Base\Exceptions;

class BaseException extends \Exception
{

    const CONNECTION_ERROR = 0x0001;
    const VALIDATION_ERROR = 0x0002;
    const CALLBACK_VERIFICATION_FAILED = 0x0003;
    const CANNOT_PARSE_CALLBACK = 0x0004;
    const UNKNOWN_CALLBACK_TYPE = 0x0005;
    const API_REQUEST_ERROR = 0x0006;
    const UNKNOWN_ERROR = 0x1000;

    public function __construct($message = "", $error_code = self::UNKNOWN_ERROR, $exception = null)
    {
        parent::__construct($message, $error_code, $exception);
    }
}