<?php

namespace Mitoop\Http\Resources;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Resources\Json\JsonResource;

class Resource extends JsonResource
{
    use ResourceTrait;

    public function toResponse($request): JsonResponse
    {
        return $this->encoding(parent::toResponse($request));
    }

    public static function collection($resource): AnonymousResourceCollection
    {
        $collection = parent::collection($resource);

        $collection->with = (new self([]))->with;

        return $collection;
    }
}
