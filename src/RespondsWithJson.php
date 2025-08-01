<?php

namespace Mitoop\Http;

use Illuminate\Http\JsonResponse;
use stdClass;

trait RespondsWithJson
{
    public function success(array|object|null $data = new stdClass, string $message = 'ok'): JsonResponse
    {
        return app(JsonResponder::class)->success($data, $message);
    }

    public function error(string $message = 'error', ?int $code = null, array|object|null $data = new stdClass): JsonResponse
    {
        return app(JsonResponder::class)->error($message, $code, $data);
    }

    public function deny(string $message = 'unauthorized', array|object|null $data = new stdClass): JsonResponse
    {
        return app(JsonResponder::class)->deny($message, $data);
    }
}
