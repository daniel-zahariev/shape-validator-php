<?php

namespace DanielZ\ShapeValidator;

use Exception;
use Throwable;

/**
 * Class ShapeException
 * @package DanielZ\ShapeValidator
 */
class ShapeException extends Exception
{
    /**
     * @var array A list of error messages for every field (array key)
     */
    protected $validationErrors;

    public function __construct($message = "", $code = 0, Throwable $previous = null, $validationErrors = [])
    {
        parent::__construct($message, $code, $previous);

        $this->validationErrors = $validationErrors;
    }

    /**
     * @return array A list of error messages for every field (array key)
     */
    public function getValidationErrors()
    {
        return $this->validationErrors;
    }
}