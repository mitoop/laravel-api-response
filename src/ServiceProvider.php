<?php

namespace Mitoop\Http;

use Mitoop\Http\Headers\HeaderResolverInterface;
use Mitoop\Http\Headers\NullHeaderResolver;

class ServiceProvider extends \Illuminate\Support\ServiceProvider
{
    public array $singletons = [
        Config::class => Config::class,
        Responder::class => Responder::class,
        ResponseGenerator::class => ResponseGenerator::class,
        HeaderResolverInterface::class => NullHeaderResolver::class,
    ];
}
