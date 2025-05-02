<?php

namespace Mitoop\Http\Headers;

interface HeaderResolverInterface
{
    public function resolve(array $payload, int $jsonOptions): array;
}
