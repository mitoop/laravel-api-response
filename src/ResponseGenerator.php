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
            $meta = $this->getPaginationMeta($data);
            $data = $data->getCollection();
        } elseif ($data instanceof CursorPaginator) {
            $meta = [
                'next_cursor' => $data->nextCursor()?->encode(),
                'page_size' => $data->perPage(),
                'has_more' => $data->hasMorePages(),
            ];
            $data = $data->items();
        }

        $payload = $this->preparePayload($data, $message, $code, $meta);

        return Response::json($payload, options: JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    protected function getPaginationMeta(Paginator $paginator): array
    {
        $meta = [
            'page' => $paginator->currentPage(),
            'page_size' => $paginator->perPage(),
            'has_more' => $paginator->hasMorePages(),
        ];

        if (method_exists($paginator, 'total')) {
            $meta['total'] = (int) $paginator->total();
        }

        return $meta;
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
