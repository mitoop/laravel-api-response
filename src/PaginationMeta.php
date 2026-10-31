<?php

declare(strict_types=1);

namespace Mitoop\Http;

use Illuminate\Contracts\Pagination\CursorPaginator;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
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
            return [$data->items(), self::from($data)];
        }

        return [$data, []];
    }

    /**
     * @return array<string, mixed>
     */
    public static function from(mixed $data): array
    {
        if ($data instanceof Paginator) {
            return self::page($data);
        }

        if ($data instanceof CursorPaginator) {
            return self::cursor($data);
        }

        return [];
    }

    /**
     * @return array<string, mixed>
     */
    private static function page(Paginator $paginator): array
    {
        $meta = [
            'pagination' => 'page',
            'page' => (int) $paginator->currentPage(),
            'page_size' => (int) $paginator->perPage(),
            'has_more' => (bool) $paginator->hasMorePages(),
        ];

        if ($paginator instanceof LengthAwarePaginator) {
            $meta['total'] = (int) $paginator->total();
        }

        return $meta;
    }

    /**
     * @return array<string, mixed>
     */
    private static function cursor(CursorPaginator $paginator): array
    {
        return [
            'pagination' => 'cursor',
            'page_size' => (int) $paginator->perPage(),
            'has_more' => (bool) $paginator->hasMorePages(),
            'next_cursor' => $paginator->nextCursor()?->encode(),
        ];
    }
}
