<?php

namespace Mitoop\Http\Headers;

class NullHeaderResolver implements HeaderResolverInterface
{
    public function resolve(array $payload, int $jsonOptions): array
    {
        return [];
    }
}
