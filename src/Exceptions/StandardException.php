<?php

namespace Mitoop\Http\Exceptions;

use Exception;
use Throwable;

class StandardException extends Exception
{
    public function __construct(string $message, ?Throwable $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }
}
