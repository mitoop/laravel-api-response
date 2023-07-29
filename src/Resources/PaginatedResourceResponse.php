<?php

namespace Mitoop\Http\Resources;

use Illuminate\Http\Resources\Json\PaginatedResourceResponse as Base;

class PaginatedResourceResponse extends Base
{
    protected function paginationInformation($request): array
    {
        $paginator = $this->resource->resource;

        $currentPage = (int) $paginator->currentPage();

        $pageMeta = [
            'page' => $currentPage,
            'page_size' => (int) $paginator->perPage(),
            'has_more' => $paginator->hasMorePages(),
        ];

        if (method_exists($paginator, 'total')) {
            $pageMeta['total'] = (int) $paginator->total();
        }

        return [
            'meta' => $pageMeta,
        ];
    }
}
