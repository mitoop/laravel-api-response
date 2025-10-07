<?php

namespace Mitoop\Http\Exceptions;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\MultipleRecordsFoundException;
use Illuminate\Database\RecordNotFoundException;
use Illuminate\Database\RecordsNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;
use Mitoop\Http\JsonResponder;
use stdClass;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    protected JsonResponder $responder;

    public function register()
    {
        $this->responder = $this->container->make(JsonResponder::class);

        $this->renderable(function (Throwable $e, Request $request) {
            return $this->renderExceptionWithJsonResponder($e, $request);
        });
    }

    public function map($from, $to = null)
    {
        if (is_array($from)) {
            $defaults = [
                ModelNotFoundException::class => fn ($e) => new NotFoundHttpException('Model not found', $e),
                RecordNotFoundException::class => fn ($e) => new NotFoundHttpException('Record not found', $e),
                RecordsNotFoundException::class => fn ($e) => new NotFoundHttpException('Records not found', $e),
                MultipleRecordsFoundException::class => fn ($e) => new ClientSafeException($e->getMessage(), $e),
            ];

            $from = array_merge($defaults, $from);

            foreach ($from as $key => $value) {
                parent::map($key, $value);
            }

            return $this;
        }

        return parent::map($from, $to);
    }

    protected function renderExceptionWithJsonResponder(Throwable $e, Request $request): JsonResponse
    {
        if ($e instanceof ClientSafeException) {
            return $this->responder->error($e->getMessage(), $e->getErrorCode());
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
        if (config('app.debug')) {
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
