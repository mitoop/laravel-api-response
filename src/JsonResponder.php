<?php

namespace Mitoop\Http;

use Illuminate\Http\JsonResponse;
use stdClass;

class JsonResponder
{
    public function __construct(protected ResponseGenerator $generator, protected JsonResponderDefault $config) {}

    public function success($data = new stdClass, string $message = 'ok'): JsonResponse
    {
        return $this->generator->generate($data, $message, $this->config->success());
    }

    public function error(string $message = 'error', ?int $code = null, $data = new stdClass): JsonResponse
    {
        return $this->generator->generate($data, $message, $code ?? $this->config->error());
    }

    public function deny(string $message = 'failed', $data = new stdClass): JsonResponse
    {
        return $this->generator->generate($data, $message, $this->config->deny());
    }
}
