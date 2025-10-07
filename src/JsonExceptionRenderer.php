<?php

namespace Mitoop\Http;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;
use stdClass;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Throwable;

class JsonExceptionRenderer
{
    public function __construct(protected JsonResponder $responder) {}

    public function render(Throwable $e, Request $request): JsonResponse
    {
        if ($e instanceof AuthenticationException) {
            return $this->responder->deny('Unauthenticated');
        }

        if ($e instanceof ValidationException) {
            return $this->responder->error(Arr::first(Arr::flatten($e->errors())), $e->status, $e->errors());
        }

        if ($e instanceof HttpException) {
            return $this->responder->error($e->getMessage(), $e->getStatusCode());
        }

        $errors = new stdClass;
        if (app()->environment('local')) {
            $errors = [
                'class' => get_class($e),
                'code' => $e->getCode(),
                'message' => $e->getMessage(),
                'file' => sprintf('%s:%d', $e->getFile(), $e->getLine()),
            ];
        }

        return $this->responder->error('Something went wrong', 500, $errors);
    }
}
