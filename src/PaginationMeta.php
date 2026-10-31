<?php

declare(strict_types=1);

namespace Mitoop\Http;

use Illuminate\Contracts\Pagination\CursorPaginator;
use Illuminate\Contracts\Pagination\Paginator;

final class PaginationMeta
{
    private function __construct() {}

    /**
     * @return array{0: mixed, 1: array<string, mixed>}
     */
    public static function split(mixed $data): array
    {
        if ($data instanceof Paginator || $data instanceof CursorPaginator) {
            $items = method_exists($data, 'getCollection') ? $data->getCollection() : $data->items();

            return [$items, self::from($data)];
        }

        return [$data, []];
    }

    /**
     * @return array<string, mixed>
     */
    public static function from(mixed $data): array
    {
        if ($data instanceof Paginator) {
            $meta = [
                'pagination' => 'page',
                'page' => (int) $data->currentPage(),
                'page_size' => (int) $data->perPage(),
                'has_more' => (bool) $data->hasMorePages(),
            ];

            if (method_exists($data, 'total')) {
                $meta['total'] = (int) $data->total();
            }

            return $meta;
        }

        if ($data instanceof CursorPaginator) {
            return [
                'pagination' => 'cursor',
                'page_size' => (int) $data->perPage(),
                'has_more' => (bool) $data->hasMorePages(),
                'next_cursor' => $data->nextCursor()?->encode(),
            ];
        }

        return [];
    }
}
