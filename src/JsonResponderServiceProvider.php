<?php

namespace Mitoop\Http;

use Illuminate\Support\ServiceProvider;
use Mitoop\Http\Headers\HeaderResolverInterface;
use Mitoop\Http\Headers\NullHeaderResolver;

class JsonResponderServiceProvider extends ServiceProvider
{
    public array $singletons = [
        JsonResponderDefault::class => JsonResponderDefault::class,
        JsonResponder::class => JsonResponder::class,
        ResponseGenerator::class => ResponseGenerator::class,
        HeaderResolverInterface::class => NullHeaderResolver::class,
        JsonExceptionRenderer::class => JsonExceptionRenderer::class,
    ];
}
