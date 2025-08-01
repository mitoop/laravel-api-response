<?php

namespace Mitoop\Http;

use Illuminate\Contracts\Pagination\CursorPaginator;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Response;
use Mitoop\Http\Headers\HeaderResolverInterface;

class ResponseGenerator
{
    public function __construct(protected HeaderResolverInterface $headerResolver, protected JsonResponderDefault $config) {}

    public function generate($data, string $message, int $code): JsonResponse
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

        $payload = $this->preparePayload($data, $message, $code, $meta);
        $jsonOptions = JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES;
        $headers = $this->headerResolver->resolve($payload, $jsonOptions);

        return Response::json($payload, headers: $headers, options: $jsonOptions);
    }

    protected function preparePayload($data, string $message, int $code, array $meta): array
    {
        if (is_scalar($data)) {
            $data = ['value' => $data];
        }

        $payload = [
            'code' => $code,
            'message' => $message,
            'data' => $data,
            'meta' => (object) $meta,
        ];

        $extra = $this->config->extra();
        if (! empty($extra)) {
            $payload = array_merge($payload, $extra);
        }

        return $payload;
    }
}
