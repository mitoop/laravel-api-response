<?php

namespace Mitoop\Http;

use Illuminate\Http\JsonResponse;

class Responder
{
    public function __construct(protected ResponseGenerator $generator, protected Config $config) {}

    public function success($data, $message): JsonResponse
    {
        return $this->generator->generate($data, $message, $this->config->success());
    }

    public function error($message, $code, $data): JsonResponse
    {
        return $this->generator->generate($data, $message, $code ?? $this->config->error());
    }

    public function reject($message, $data): JsonResponse
    {
        return $this->generator->generate($data, $message, $this->config->reject());
    }
}
