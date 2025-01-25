<?php

namespace Mitoop\Http;

class ServiceProvider extends \Illuminate\Support\ServiceProvider
{
    public array $singletons = [
        Config::class => Config::class,
        Responder::class => Responder::class,
        ResponseGenerator::class => ResponseGenerator::class,
    ];
}
