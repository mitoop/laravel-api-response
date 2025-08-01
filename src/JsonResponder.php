<?php

namespace Mitoop\Http;

use Illuminate\Http\JsonResponse;
use stdClass;

class JsonResponder
{
    public function __construct(protected ResponseGenerator $generator, protected Config $config) {}

    public function success($data = new stdClass, $message = 'ok'): JsonResponse
    {
        return $this->generator->generate($data, $message, $this->config->success());
    }

    public function error($message = 'error', ?int $code = null, $data = new stdClass): JsonResponse
    {
        return $this->generator->generate($data, $message, $code ?? $this->config->error());
    }

    public function deny($message = 'failed', $data = new stdClass): JsonResponse
    {
        return $this->generator->generate($data, $message, $this->config->deny());
    }
}
