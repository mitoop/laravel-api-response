<?php

namespace Mitoop\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class Resource extends JsonResource
{
    use ResourceTrait;

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
