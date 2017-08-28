<?php namespace SDK\Base\Exceptions;

class ArgumentsException extends BaseException
{
    /**
     * @var array
     */
    protected $errors = [];
    protected $message = "Request failed due to validation error";

    /**
     * APIArgumentsException constructor.
     * @param array $errors
     */
    public function __construct(array $errors)
    {
        $this->errors = $errors;
        parent::__construct($this->message . ": " . json_encode($this->getValidationError()), BaseException::VALIDATION_ERROR);
    }

    /**
     * @return array
     */
    public function getValidationError()
    {
        return $this->errors;
    }
}