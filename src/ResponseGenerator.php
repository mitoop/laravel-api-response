<?php

namespace Mitoop\Http;

use Illuminate\Contracts\Pagination\CursorPaginator;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Response;

class ResponseGenerator
{
    protected Config $config;

    public function __construct(Config $config)
    {
        $this->config = $config;
    }

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
                $meta['total'] = (int) $data->total();
            }
            $data = $data->getCollection();
        } elseif ($data instanceof CursorPaginator) {
            $meta = [
                'pagination' => 'cursor',
                'next_cursor' => $data->nextCursor()?->encode(),
                'page_size' => $data->perPage(),
                'has_more' => $data->hasMorePages(),
            ];
            $data = $data->items();
        }

        return Response::json(
            $this->preparePayload($data, $message, $code, $meta),
            options: JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
        );
    }

    protected function preparePayload($data, string $message, int $code, array $meta): array
    {
        $payload = [
            'code' => $code,
            'message' => $message,
            'data' => $data,
        ];

        if ($meta) {
            $payload['meta'] = $meta;
        }

        $extra = $this->config->extra();
        if (! empty($extra)) {
            $payload = array_merge($payload, $extra);
        }

        return $payload;
    }
}
