<?php

declare(strict_types=1);

namespace Mitoop\Http;

use Illuminate\Http\JsonResponse;
use Mitoop\Http\Headers\HeaderResolverInterface;
use stdClass;

class EnvelopeFactory
{
    public const JSON_OPTIONS = JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR;

    public function __construct(
        protected HeaderResolverInterface $headerResolver,
        protected ResponderConfig $config,
    ) {}

    public function generateSuccess(mixed $data, string $message, int $code, int $status = 200): JsonResponse
    {
        [$data, $meta] = PaginationMeta::split($data);

        return $this->send($this->successPayload($data, $message, $code, $meta), $status);
    }

    public function generateError(string $message, int $code, mixed $errors, int $status = 200): JsonResponse
    {
        return $this->send($this->errorPayload($message, $code, $errors), $status);
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    public function generateResource(mixed $data, mixed $source, array $overrides = [], ?int $status = null): JsonResponse
    {
        $message = isset($overrides['message']) && is_string($overrides['message'])
            ? $overrides['message']
            : $this->config->successMessage();

        return $this->send(
            $this->successPayload(
                $data,
                $message,
                $this->config->success(),
                PaginationMeta::from($source),
                $this->config->mergedExtra($overrides),
            ),
            $status ?? $this->config->successStatus(),
        );
    }

    /**
     * @param  array<string, mixed>  $meta
     * @param  array<string, mixed>|null  $extra
     * @return array<string, mixed>
     */
    protected function successPayload(mixed $data, string $message, int $code, array $meta, ?array $extra = null): array
    {
        $payload = [
            'success' => true,
            'code' => $code,
            'message' => $message,
            'data' => $data ?? new stdClass,
        ];

        if ($meta !== []) {
            $payload['meta'] = $meta;
        }

        return array_merge($payload, $extra ?? $this->config->extra());
    }

    /**
     * @return array<string, mixed>
     */
    protected function errorPayload(string $message, int $code, mixed $errors): array
    {
        return array_merge([
            'success' => false,
            'code' => $code,
            'message' => $message,
            'data' => new stdClass,
            'errors' => $errors ?? new stdClass,
        ], $this->config->extra());
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    protected function send(array $payload, int $status): JsonResponse
    {
        return new JsonResponse(
            $payload,
            $status,
            $this->headerResolver->resolve($payload, self::JSON_OPTIONS),
            self::JSON_OPTIONS,
        );
    }
}
