<?php namespace SDK\Base\Exceptions;


class UnsupportedMimeType extends BaseException
{
    protected $message = "Unsupported MIME type";

    /**
     * Class constructor.
     * @param string $type
     */
    public function __construct($type)
    {
        $message = $this->message . ' \'' . $type . '\'';
        parent::__construct($message, BaseException::UNSUPPORTED_MIME_TYPE);
    }
}