<?php

namespace Mitoop\Http;

use Illuminate\Http\JsonResponse;
use stdClass;

trait RespondsWithJson
{
    public function success($data = new stdClass, $message = 'ok'): JsonResponse
    {
        return app(JsonResponder::class)->success($data, $message);
    }

    public function error($message = 'error', ?int $code = null, $data = new stdClass): JsonResponse
    {
        return app(JsonResponder::class)->error($message, $code, $data);
    }

    public function deny($message = 'unauthorized', $data = new stdClass): JsonResponse
    {
        return app(JsonResponder::class)->deny($message, $data);
    }
}
