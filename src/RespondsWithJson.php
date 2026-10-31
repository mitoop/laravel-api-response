<?php

declare(strict_types=1);

namespace Mitoop\Http;

use Illuminate\Http\JsonResponse;
use stdClass;

trait RespondsWithJson
{
    public function success(mixed $data = new stdClass, ?string $message = null, ?int $status = null): JsonResponse
    {
        return app(Responder::class)->success($data, $message, $status);
    }

    public function error(?string $message = null, ?int $code = null, mixed $errors = new stdClass, ?int $status = null): JsonResponse
    {
        return app(Responder::class)->error($message, $code, $errors, $status);
    }

    public function deny(?string $message = null, mixed $errors = new stdClass, ?int $status = null): JsonResponse
    {
        return app(Responder::class)->deny($message, $errors, $status);
    }
}
