<?php

declare(strict_types=1);

use Illuminate\Config\Repository;
use Illuminate\Filesystem\FilesystemServiceProvider;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Bootstrap\HandleExceptions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Facade;
use Illuminate\Testing\TestResponse;
use Illuminate\Translation\TranslationServiceProvider;
use Illuminate\View\ViewServiceProvider;
use Mitoop\Http\ResponderServiceProvider;
use PHPUnit\Framework\TestCase;

abstract class PackageTestCase extends TestCase
{
    protected Application $app;

    protected function setUp(): void
    {
        $this->app = new Application(__DIR__);
        $this->app->instance('config', new Repository([
            'app' => ['asset_url' => null, 'locale' => 'en', 'fallback_locale' => 'en'],
            'view' => ['paths' => [], 'compiled' => __DIR__.'/compiled'],
        ]));
        $this->app->register(FilesystemServiceProvider::class);
        $this->app->register(ViewServiceProvider::class);
        $this->app->register(TranslationServiceProvider::class);
        $this->app->register(ResponderServiceProvider::class);
        Facade::clearResolvedInstances();
        Facade::setFacadeApplication($this->app);
    }

    protected function tearDown(): void
    {
        Facade::clearResolvedInstances();
        Facade::setFacadeApplication(null);
        $this->app->flush();
        Application::setInstance(null);
    }

    protected function dispatch(string $uri): TestResponse
    {
        $request = Request::create($uri, 'GET', server: ['HTTP_ACCEPT' => 'application/json']);
        $this->app->instance('request', $request);
        $previousErrorReporting = error_reporting(-1);
        set_error_handler([new HandleExceptions, 'handleError'], E_WARNING | E_NOTICE);

        try {
            return TestResponse::fromBaseResponse($this->app['router']->dispatch($request));
        } finally {
            restore_error_handler();
            error_reporting($previousErrorReporting);
        }
    }
}
