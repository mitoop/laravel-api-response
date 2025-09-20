<?php

namespace Mitoop\Http;

use Illuminate\Http\JsonResponse;
use stdClass;

class JsonResponder
{
    public function __construct(protected ResponseGenerator $generator, protected JsonResponderDefault $config) {}

    public function success($data = new stdClass, string $message = 'ok'): JsonResponse
    {
        return $this->generator->generateSuccess($data, $message, $this->config->success());
    }

    public function error(string $message = 'error', ?int $code = null, $errors = new stdClass): JsonResponse
    {
        return $this->generator->generateError($message, $code ?? $this->config->error(), $errors);
    }

    public function deny(string $message = 'unauthorized', $errors = new stdClass): JsonResponse
    {
        return $this->generator->generateError($message, $this->config->deny(), $errors);
    }
}
