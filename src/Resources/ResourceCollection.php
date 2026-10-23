<?php

namespace Mitoop\Http\Resources;

use Illuminate\Contracts\Pagination\CursorPaginator;
use Illuminate\Http\Resources\Json\ResourceCollection as Base;

class ResourceCollection extends Base
{
    use ResourceTrait;

    public function paginationInformation($request, $paginated, $default): array
    {
        if ($this->resource instanceof CursorPaginator) {
            $meta = [
                'pagination' => 'cursor',
                'next_cursor' => (string) $this->resource->nextCursor()?->encode(),
                'page_size' => $this->resource->perPage(),
                'has_more' => $this->resource->hasMorePages(),
            ];

            return compact('meta');
        }

        $meta = [
            'page' => $paginated['current_page'],
            'page_size' => $paginated['per_page'],
            'has_more' => $this->resource->hasMorePages(),
        ];

        if (method_exists($this->resource, 'total')) {
            $meta['total'] = (int) $this->resource->total();
        }

        return compact('meta');
    }
}
