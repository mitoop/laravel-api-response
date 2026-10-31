<?php

declare(strict_types=1);

require_once __DIR__.'/PackageTestCase.php';

use Illuminate\Http\Request;
use Illuminate\Pagination\Cursor;
use Illuminate\Pagination\CursorPaginator;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Testing\TestResponse;
use Mitoop\Http\Resources\Resource;
use Mitoop\Http\Responder;

final class FixtureResource extends Resource
{
    public function toArray(Request $request): array
    {
        return ['id' => $this->resource['id'], 'label' => strtoupper($this->resource['label'])];
    }
}

final class ResourcePaginationTest extends PackageTestCase
{
    public function test_cursor_resource_response_preserves_transformation_and_cursor_metadata(): void
    {
        $paginator = $this->paginator('cursor');
        $this->app['router']->get('/items', fn () => FixtureResource::collection($paginator));

        $response = $this->dispatch('/items');

        $response->assertOk()->assertJsonPath('data', [['id' => 1, 'label' => 'FIRST']]);
        $response->assertJsonPath('success', true);
        $response->assertJsonPath('code', 0);
        $response->assertJsonPath('message', 'ok');
        $response->assertJsonMissingPath('errors');
        $response->assertJsonPath('meta.pagination', 'cursor');
        $response->assertJsonPath('meta.next_cursor', (new Cursor(['id' => 1], true))->encode());
        $response->assertJsonPath('meta.page_size', 1)->assertJsonPath('meta.has_more', true);
        $response->assertJsonMissingPath('meta.page')->assertJsonMissingPath('meta.total');
    }

    public function test_length_aware_resource_response_includes_page_type(): void
    {
        $this->app['router']->get('/items', fn () => FixtureResource::collection($this->paginator('length')));

        $response = $this->dispatch('/items');

        $response->assertOk()->assertJsonPath('data', [['id' => 1, 'label' => 'FIRST']]);
        $response->assertJsonMissingPath('errors');
        $response->assertJsonPath('meta', [
            'pagination' => 'page',
            'page' => 1,
            'page_size' => 1,
            'has_more' => true,
            'total' => 2,
        ]);
    }

    public function test_terminal_cursor_resource_response_has_null_next_cursor(): void
    {
        $this->app['router']->get('/items', fn () => FixtureResource::collection($this->paginator('cursor-end')));

        $response = $this->dispatch('/items');

        $response->assertOk()->assertJsonPath('data', [['id' => 2, 'label' => 'LAST']]);
        $response->assertJsonPath('meta', [
            'pagination' => 'cursor',
            'page_size' => 1,
            'has_more' => false,
            'next_cursor' => null,
        ]);
    }

    public function test_empty_cursor_resource_response_has_empty_data_and_null_next_cursor(): void
    {
        $this->app['router']->get('/items', fn () => FixtureResource::collection($this->paginator('cursor-empty')));

        $response = $this->dispatch('/items');

        $response->assertOk()->assertJsonPath('data', []);
        $response->assertJsonPath('meta', [
            'pagination' => 'cursor',
            'page_size' => 1,
            'has_more' => false,
            'next_cursor' => null,
        ]);
    }

    public function test_simple_resource_response_includes_page_type_without_total(): void
    {
        $this->app['router']->get('/items', fn () => FixtureResource::collection($this->paginator('simple')));

        $response = $this->dispatch('/items');

        $response->assertOk()->assertJsonPath('data', [['id' => 1, 'label' => 'FIRST']]);
        $response->assertJsonPath('meta', [
            'pagination' => 'page',
            'page' => 1,
            'page_size' => 1,
            'has_more' => true,
        ]);
    }

    public function test_direct_cursor_response_exposes_cursor_metadata(): void
    {
        $this->app['router']->get('/items', fn () => $this->app->make(Responder::class)->success($this->paginator('cursor')));

        $response = $this->dispatch('/items');

        $response->assertOk()->assertJsonPath('data', [['id' => 1, 'label' => 'first']]);
        $response->assertJsonMissingPath('errors');
        $response->assertJsonPath('meta', [
            'pagination' => 'cursor',
            'page_size' => 1,
            'has_more' => true,
            'next_cursor' => (new Cursor(['id' => 1], true))->encode(),
        ]);
    }

    public function test_resource_meta_matches_direct_response(): void
    {
        foreach (['length', 'simple', 'cursor', 'cursor-end', 'cursor-empty'] as $kind) {
            $direct = $this->app->make(Responder::class)->success($this->paginator($kind));
            $request = Request::create('/items');
            $this->app->instance('request', $request);
            $resource = FixtureResource::collection($this->paginator($kind))->toResponse($request);

            $this->assertSame(
                TestResponse::fromBaseResponse($direct)->json('meta'),
                TestResponse::fromBaseResponse($resource)->json('meta'),
                $kind,
            );
        }
    }

    private function paginator(string $kind): LengthAwarePaginator|Paginator|CursorPaginator
    {
        return match ($kind) {
            'length' => new LengthAwarePaginator([['id' => 1, 'label' => 'first']], 2, 1, 1, ['path' => '/items']),
            'simple' => new Paginator([
                ['id' => 1, 'label' => 'first'],
                ['id' => 2, 'label' => 'second'],
            ], 1, 1, ['path' => '/items']),
            'cursor' => new CursorPaginator([
                ['id' => 1, 'label' => 'first'],
                ['id' => 2, 'label' => 'second'],
            ], 1, null, ['path' => '/items', 'parameters' => ['id']]),
            'cursor-end' => new CursorPaginator([['id' => 2, 'label' => 'last']], 1, new Cursor(['id' => 1]), ['path' => '/items', 'parameters' => ['id']]),
            'cursor-empty' => new CursorPaginator([], 1, null, ['path' => '/items', 'parameters' => ['id']]),
        };
    }
}
