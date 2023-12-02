<?php

namespace Mitoop\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection as Base;

class ResourceCollection extends Base
{
    use ResourceTrait;

    public function paginationInformation($request, $paginated, $default): array
    {
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
