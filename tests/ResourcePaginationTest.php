<?php

use Illuminate\Config\Repository;
use Illuminate\Filesystem\FilesystemServiceProvider;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Bootstrap\HandleExceptions;
use Illuminate\Http\Request;
use Illuminate\Pagination\Cursor;
use Illuminate\Pagination\CursorPaginator;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Facade;
use Illuminate\Testing\TestResponse;
use Illuminate\Translation\TranslationServiceProvider;
use Illuminate\View\ViewServiceProvider;
use Mitoop\Http\JsonResponder;
use Mitoop\Http\JsonResponderServiceProvider;
use Mitoop\Http\Resources\Resource;
use PHPUnit\Framework\TestCase;

final class FixtureResource extends Resource
{
    public function toArray(Request $request): array
    {
        return ['id' => $this->resource['id'], 'label' => strtoupper($this->resource['label'])];
    }
}

final class ResourcePaginationTest extends TestCase
{
    private Application $app;

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
        $this->app->register(JsonResponderServiceProvider::class);
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

    public function test_cursor_resource_response_preserves_transformation_and_cursor_metadata(): void
    {
        $paginator = new CursorPaginator([
            ['id' => 1, 'label' => 'first'],
            ['id' => 2, 'label' => 'second'],
        ], 1, null, ['path' => '/items', 'parameters' => ['id']]);
        $this->app['router']->get('/items', fn () => FixtureResource::collection($paginator));

        $response = $this->dispatch('/items');

        $response->assertOk()->assertJsonPath('data', [['id' => 1, 'label' => 'FIRST']]);
        $response->assertJsonPath('meta.pagination', 'cursor');
        $response->assertJsonPath('meta.next_cursor', (new Cursor(['id' => 1], true))->encode());
        $response->assertJsonPath('meta.page_size', 1)->assertJsonPath('meta.has_more', true);
        $response->assertJsonMissingPath('meta.page')->assertJsonMissingPath('meta.total');
    }

    public function test_length_aware_resource_response_keeps_existing_page_contract(): void
    {
        $paginator = new LengthAwarePaginator([['id' => 1, 'label' => 'first']], 2, 1, 1, ['path' => '/items']);
        $this->app['router']->get('/items', fn () => FixtureResource::collection($paginator));

        $response = $this->dispatch('/items');

        $response->assertOk()->assertJsonPath('data', [['id' => 1, 'label' => 'FIRST']]);
        $response->assertJsonPath('meta', ['page' => 1, 'page_size' => 1, 'has_more' => true, 'total' => 2]);
    }

    public function test_terminal_cursor_resource_response_has_no_next_cursor(): void
    {
        $paginator = new CursorPaginator([['id' => 2, 'label' => 'last']], 1, new Cursor(['id' => 1]), ['path' => '/items', 'parameters' => ['id']]);
        $this->app['router']->get('/items', fn () => FixtureResource::collection($paginator));

        $response = $this->dispatch('/items');

        $response->assertOk()->assertJsonPath('data', [['id' => 2, 'label' => 'LAST']]);
        $response->assertJsonPath('meta', ['pagination' => 'cursor', 'next_cursor' => '', 'page_size' => 1, 'has_more' => false]);
    }

    public function test_empty_cursor_resource_response_has_empty_data_and_no_next_cursor(): void
    {
        $paginator = new CursorPaginator([], 1, null, ['path' => '/items', 'parameters' => ['id']]);
        $this->app['router']->get('/items', fn () => FixtureResource::collection($paginator));

        $response = $this->dispatch('/items');

        $response->assertOk()->assertJsonPath('data', []);
        $response->assertJsonPath('meta', ['pagination' => 'cursor', 'next_cursor' => '', 'page_size' => 1, 'has_more' => false]);
    }

    public function test_simple_resource_response_keeps_existing_page_contract_without_total(): void
    {
        $paginator = new Paginator([
            ['id' => 1, 'label' => 'first'],
            ['id' => 2, 'label' => 'second'],
        ], 1, 1, ['path' => '/items']);
        $this->app['router']->get('/items', fn () => FixtureResource::collection($paginator));

        $response = $this->dispatch('/items');

        $response->assertOk()->assertJsonPath('data', [['id' => 1, 'label' => 'FIRST']]);
        $response->assertJsonPath('meta', ['page' => 1, 'page_size' => 1, 'has_more' => true]);
    }

    public function test_direct_cursor_response_already_exposes_cursor_metadata(): void
    {
        $paginator = new CursorPaginator([
            ['id' => 1, 'label' => 'first'],
            ['id' => 2, 'label' => 'second'],
        ], 1, null, ['path' => '/items', 'parameters' => ['id']]);
        $this->app['router']->get('/items', fn () => $this->app->make(JsonResponder::class)->success($paginator));

        $response = $this->dispatch('/items');

        $response->assertOk()->assertJsonPath('data', [['id' => 1, 'label' => 'first']]);
        $response->assertJsonPath('meta', [
            'pagination' => 'cursor',
            'next_cursor' => (new Cursor(['id' => 1], true))->encode(),
            'page_size' => 1,
            'has_more' => true,
        ]);
    }

    private function dispatch(string $uri): TestResponse
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
