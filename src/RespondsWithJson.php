<?php

namespace Mitoop\Http;

use Illuminate\Http\JsonResponse;
use stdClass;

trait RespondsWithJson
{
    public function success($data = new stdClass, string $message = 'ok'): JsonResponse
    {
        return app(JsonResponder::class)->success($data, $message);
    }

    public function error(string $message = 'error', ?int $code = null, $data = new stdClass): JsonResponse
    {
        return app(JsonResponder::class)->error($message, $code, $data);
    }

    public function deny(string $message = 'unauthorized', $data = new stdClass): JsonResponse
    {
        return app(JsonResponder::class)->deny($message, $data);
    }
}
