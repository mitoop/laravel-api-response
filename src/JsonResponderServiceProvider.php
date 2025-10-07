<?php

namespace Mitoop\Http;

use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Support\ServiceProvider;
use Mitoop\Http\Exceptions\Handler;
use Mitoop\Http\Headers\HeaderResolverInterface;
use Mitoop\Http\Headers\NullHeaderResolver;

class JsonResponderServiceProvider extends ServiceProvider
{
    public array $singletons = [
        JsonResponderDefault::class => JsonResponderDefault::class,
        JsonResponder::class => JsonResponder::class,
        ResponseGenerator::class => ResponseGenerator::class,
        HeaderResolverInterface::class => NullHeaderResolver::class,
        ExceptionHandler::class => Handler::class,
    ];
}
