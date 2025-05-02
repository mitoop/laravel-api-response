<?php

namespace Mitoop\Http;

use Illuminate\Http\JsonResponse;
use stdClass;

trait JsonResponder
{
    public function success($data = new stdClass, $message = 'ok'): JsonResponse
    {
        return app(Responder::class)->success($data, $message);
    }

    public function error($message = 'error', ?int $code = null, $data = new stdClass): JsonResponse
    {
        return app(Responder::class)->error($message, $code, $data);
    }

    public function reject($message = 'failed', $data = new stdClass): JsonResponse
    {
        return app(Responder::class)->reject($message, $data);
    }
}
