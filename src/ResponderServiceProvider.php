<?php

declare(strict_types=1);

namespace Mitoop\Http;

use Illuminate\Support\ServiceProvider;
use Mitoop\Http\Headers\HeaderResolverInterface;
use Mitoop\Http\Headers\NullHeaderResolver;

class ResponderServiceProvider extends ServiceProvider
{
    public array $singletons = [
        ResponderConfig::class => ResponderConfig::class,
        Responder::class => Responder::class,
        EnvelopeFactory::class => EnvelopeFactory::class,
        HeaderResolverInterface::class => NullHeaderResolver::class,
    ];
}
