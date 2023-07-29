<?php

namespace Mitoop\Http\Resources;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\ResourceCollection as Base;
use Mitoop\Http\ResponseCode;

class ResourceCollection extends Base
{
    use ResourceTrait;

    public $with = [
        'code' => ResponseCode::SUCCESS,
        'message' => 'success',
    ];

    public function preparePaginatedResponse($request): JsonResponse
    {
        if ($this->preserveAllQueryParameters) {
            $this->resource->appends($request->query());
        } elseif (! is_null($this->queryParameters)) {
            $this->resource->appends($this->queryParameters);
        }

        return (new PaginatedResourceResponse($this))->toResponse($request);
    }
}
