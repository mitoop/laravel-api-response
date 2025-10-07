<?php

namespace Mitoop\Http\Exceptions;

use Exception;
use Mitoop\Http\JsonResponderDefault;
use Throwable;

class ClientSafeException extends Exception
{
    protected int $errorCode;

    public function __construct(string $message, ?Throwable $previous = null, ?int $errorCode = null)
    {
        $this->errorCode = $errorCode ?? $this->defaultErrorCode();

        parent::__construct($message, 0, $previous);
    }

    protected function defaultErrorCode(): int
    {
        return app(JsonResponderDefault::class)->error();
    }

    public function getErrorCode(): int
    {
        return $this->errorCode;
    }
}
