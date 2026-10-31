<?php

declare(strict_types=1);

namespace Mitoop\Http\Headers;

class NullHeaderResolver implements HeaderResolverInterface
{
    public function resolve(array $payload, int $jsonOptions): array
    {
        return [];
    }
}
