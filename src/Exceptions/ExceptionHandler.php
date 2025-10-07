<?php

namespace Mitoop\Http\Exceptions;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Contracts\Debug\ExceptionHandler as IlluminateExceptionHandler;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\MultipleRecordsFoundException;
use Illuminate\Database\RecordNotFoundException;
use Illuminate\Database\RecordsNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;
use Mitoop\Http\JsonResponder;
use stdClass;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

class ExceptionHandler
{
    public function __construct(protected IlluminateExceptionHandler $handler, protected JsonResponder $responder) {}

    public function map(array $mappings): void
    {
        $defaults = [
            ModelNotFoundException::class => fn ($e) => new NotFoundHttpException('Model not found', $e),
            RecordNotFoundException::class => fn ($e) => new NotFoundHttpException('Record not found', $e),
            RecordsNotFoundException::class => fn ($e) => new NotFoundHttpException('Records not found', $e),
            MultipleRecordsFoundException::class => fn ($e) => new StandardException($e->getMessage(), $e),
        ];

        $mappings = array_merge($defaults, $mappings);

        foreach ($mappings as $from => $to) {
            $this->handler->map($from, $to);
        }
    }

    public function render(Throwable $e, Request $request): JsonResponse
    {
        if ($e instanceof StandardException) {
            return $this->responder->error($e->getMessage());
        }

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
