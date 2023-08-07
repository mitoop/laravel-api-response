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

    protected static function newCollection($resource): ResourceCollection
    {
        return new class($resource, static::class) extends ResourceCollection
        {
            public $collects;

            public function __construct($resource, $collects)
            {
                $this->collects = $collects;

                parent::__construct($resource);
            }
        };
    }
}
