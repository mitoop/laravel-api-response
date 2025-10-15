<?php

namespace Mitoop\Http;

use Illuminate\Contracts\Pagination\CursorPaginator;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Response;
use Mitoop\Http\Headers\HeaderResolverInterface;
use stdClass;

class ResponseGenerator
{
    public function __construct(
        protected HeaderResolverInterface $headerResolver,
        protected JsonResponderDefault $config
    ) {}

    public function generateSuccess($data, string $message, int $code): JsonResponse
    {
        [$data, $meta] = $this->resolvePagination($data);

        return $this->toJson(
            $this->prepareSuccessPayload($data, $message, $code, $meta)
        );
    }

    public function generateError(string $message, int $code, mixed $errors): JsonResponse
    {
        return $this->toJson(
            $this->prepareErrorPayload($message, $code, $errors)
        );
    }

    protected function resolvePagination(mixed $data): array
    {
        $meta = [];

        if ($data instanceof Paginator) {
            $meta = [
                'pagination' => 'page',
                'page' => $data->currentPage(),
                'page_size' => $data->perPage(),
                'has_more' => $data->hasMorePages(),
            ];

            if (method_exists($data, 'total')) {
                $meta['total'] = $data->total();
            }

            $data = $data->getCollection();
        } elseif ($data instanceof CursorPaginator) {
            $meta = [
                'pagination' => 'cursor',
                'next_cursor' => (string) $data->nextCursor()?->encode(),
                'page_size' => $data->perPage(),
                'has_more' => $data->hasMorePages(),
            ];

            $data = $data->items();
        }

        return [$data, $meta];
    }

    protected function prepareSuccessPayload($data, string $message, int $code, array $meta): array
    {
        $payload = [
            'success' => true,
            'code' => $code,
            'message' => $message,
            'data' => $data ?? new stdClass,
        ];

        if (! empty($meta)) {
            $payload['meta'] = $meta;
        }

        return $this->mergeExtra($payload);
    }

    protected function prepareErrorPayload(string $message, int $code, mixed $errors): array
    {
        return $this->mergeExtra([
            'success' => false,
            'code' => $code,
            'message' => $message,
            'data' => new stdClass,
            'errors' => $errors ?? new stdClass,
        ]);
    }

    protected function mergeExtra(array $payload): array
    {
        return array_merge($payload, $this->config->extra() ?: []);
    }

    protected function toJson(array $payload): JsonResponse
    {
        $options = JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES;

        return Response::json(
            $payload,
            headers: $this->headerResolver->resolve($payload, $options),
            options: $options
        );
    }
}
