<?php

declare(strict_types=1);

namespace Mitoop\Http;

use Illuminate\Http\JsonResponse;
use stdClass;

class Responder
{
    public function __construct(protected EnvelopeFactory $factory, protected ResponderConfig $config) {}

    public function success(mixed $data = new stdClass, ?string $message = null, ?int $status = null): JsonResponse
    {
        return $this->factory->generateSuccess(
            $data,
            $message ?? $this->config->successMessage(),
            $this->config->success(),
            $status ?? $this->config->successStatus(),
        );
    }

    public function error(?string $message = null, ?int $code = null, mixed $errors = new stdClass, ?int $status = null): JsonResponse
    {
        return $this->factory->generateError(
            $message ?? $this->config->errorMessage(),
            $code ?? $this->config->error(),
            $errors,
            $status ?? $this->config->errorStatus(),
        );
    }

    public function deny(?string $message = null, mixed $errors = new stdClass, ?int $status = null): JsonResponse
    {
        return $this->factory->generateError(
            $message ?? $this->config->denyMessage(),
            $this->config->deny(),
            $errors,
            $status ?? $this->config->denyStatus(),
        );
    }
}
