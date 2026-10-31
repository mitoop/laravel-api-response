<?php

declare(strict_types=1);

namespace Mitoop\Http\Headers;

interface HeaderResolverInterface
{
    public function resolve(array $payload, int $jsonOptions): array;
}
