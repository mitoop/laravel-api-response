<?php

namespace Mitoop\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Mitoop\Http\ResponseCode;

class Resource extends JsonResource
{
    use ResourceTrait;

    public $with = [
        'code' => ResponseCode::SUCCESS,
        'message' => 'success',
    ];

    protected static function newCollection($resource)
    {
        return tap(new ResourceCollection($resource), function ($collection) {
            $collection->collects = static::class;
            $collection->with = (new static([]))->with;
        });
    }
}
