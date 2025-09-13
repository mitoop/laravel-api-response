<?php

namespace Mitoop\Http;

use Illuminate\Http\JsonResponse;
use stdClass;

trait RespondsWithJson
{
    public function success(mixed $data = new stdClass, string $message = 'ok'): JsonResponse
    {
        return app(JsonResponder::class)->success($data, $message);
    }

    public function error(string $message = 'error', ?int $code = null, mixed $error = null): JsonResponse
    {
        return app(JsonResponder::class)->error($message, $code, $error);
    }

    public function deny(string $message = 'unauthorized', mixed $error = null): JsonResponse
    {
        return app(JsonResponder::class)->deny($message, $error);
    }
}
