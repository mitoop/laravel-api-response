<?php

namespace Mitoop\Http\Resources;

use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Resources\Json\JsonResource;
use Mitoop\Http\ResponseCode;

class Resource extends JsonResource
{
    use ResourceTrait;

    public $with = [
        'code' => ResponseCode::SUCCESS,
        'message' => 'success',
    ];

    protected static function newCollection($resource): AnonymousResourceCollection
    {
        return new class($resource, static::class) extends AnonymousResourceCollection {};
    }
}
