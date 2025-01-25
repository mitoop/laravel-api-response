<?php

namespace Mitoop\Http;

use Illuminate\Http\JsonResponse as IlluminateJsonResponse;

trait JsonResponder
{
    public function success($data = null, $message = 'ok'): IlluminateJsonResponse
    {
        return app(ResponseGenerator::class)->generate($data, $message, app(Config::class)->success());
    }

    public function error($message = 'error', ?int $code = null, $data = null): IlluminateJsonResponse
    {
        return app(ResponseGenerator::class)->generate($data, $message, $code ?? app(Config::class)->error());
    }

    public function reject($message = 'failed', $data = null): IlluminateJsonResponse
    {
        return app(ResponseGenerator::class)->generate($data, $message, app(Config::class)->reject());
    }
}
