<?php

declare(strict_types=1);

require_once __DIR__.'/PackageTestCase.php';

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Testing\TestResponse;
use Mitoop\Http\Headers\HeaderResolverInterface;
use Mitoop\Http\Resources\Resource;
use Mitoop\Http\Responder;
use Mitoop\Http\ResponderConfig;

final class EnvelopeResource extends Resource
{
    public function toArray(Request $request): array
    {
        return ['id' => $this->resource['id']];
    }
}

final class EnvelopeTest extends PackageTestCase
{
    public function test_success_envelope_omits_meta_and_errors(): void
    {
        $response = $this->respond($this->responder()->success());

        $response->assertOk();
        $this->assertSame(['success', 'code', 'message', 'data'], array_keys($response->json()));
        $response->assertJsonPath('message', 'ok');
        $this->assertStringContainsString('"data":{}', $response->getContent());
    }

    public function test_error_keeps_business_code_separate_from_http_status(): void
    {
        $response = $this->respond($this->responder()->error(
            '名称必填',
            errors: ['name' => ['名称必填']],
            status: 422,
        ));

        $response->assertStatus(422);
        $this->assertSame(['success', 'code', 'message', 'data', 'errors'], array_keys($response->json()));
        $response->assertJsonPath('success', false);
        $response->assertJsonPath('code', 1);
        $response->assertJsonPath('message', '名称必填');
        $response->assertJsonPath('errors', ['name' => ['名称必填']]);
        $this->assertStringNotContainsString('\u', $response->getContent());
        $this->assertStringContainsString('"data":{}', $response->getContent());
    }

    public function test_configured_messages_and_http_status_apply_until_overridden(): void
    {
        $this->app->make(ResponderConfig::class)->apply([
            'message' => [
                'success' => '成功',
                'deny' => '请登录',
            ],
            'http_status' => [
                'deny' => 401,
            ],
        ]);

        $success = $this->respond($this->responder()->success(['id' => 1]));
        $denied = $this->respond($this->responder()->deny());
        $overridden = $this->respond($this->responder()->deny('登录超时', status: 403));

        $success->assertOk()->assertJsonPath('message', '成功');
        $denied->assertStatus(401)->assertJsonPath('code', -1)->assertJsonPath('message', '请登录');
        $overridden->assertStatus(403)->assertJsonPath('message', '登录超时');
    }

    public function test_extra_closures_run_per_response_and_cannot_replace_reserved_keys(): void
    {
        $calls = 0;
        $this->app->make(ResponderConfig::class)->apply([
            'extra' => [
                'success' => false,
                'meta' => ['page' => 9],
                'request_id' => function () use (&$calls) {
                    return ++$calls;
                },
            ],
        ]);

        $first = $this->respond($this->responder()->success());
        $second = $this->respond($this->responder()->success());

        $first->assertJsonPath('success', true);
        $first->assertJsonPath('request_id', 1);
        $first->assertJsonMissingPath('meta');
        $second->assertJsonPath('request_id', 2);
        $this->assertSame(
            ['success', 'code', 'message', 'data', 'request_id'],
            array_keys($first->json()),
        );
    }

    public function test_resource_response_uses_the_same_envelope_message_status_and_headers(): void
    {
        $this->app->instance(HeaderResolverInterface::class, new class implements HeaderResolverInterface
        {
            public function resolve(array $payload, int $jsonOptions): array
            {
                return ['X-Body' => json_encode($payload, $jsonOptions)];
            }
        });

        $request = Request::create('/items');
        $this->app->instance('request', $request);
        $response = $this->respond(
            (new EnvelopeResource(['id' => 7]))
                ->withMessage('创建成功')
                ->withStatus(201)
                ->additional(['success' => false, 'trace' => 'abc'])
                ->toResponse($request)
        );

        $response->assertStatus(201);
        $response->assertHeader('X-Body', $response->getContent());
        $this->assertSame(['success', 'code', 'message', 'data', 'trace'], array_keys($response->json()));
        $response->assertJsonPath('success', true);
        $response->assertJsonPath('message', '创建成功');
        $response->assertJsonPath('data', ['id' => 7]);
        $response->assertJsonPath('trace', 'abc');
        $response->assertJsonMissingPath('meta');
        $response->assertJsonMissingPath('errors');
    }

    public function test_resource_uses_configured_success_status_and_skips_overridden_extra_closures(): void
    {
        $calls = 0;
        $this->app->make(ResponderConfig::class)->apply([
            'http_status' => ['success' => 201],
            'extra' => [
                'request_id' => function () use (&$calls) {
                    return ++$calls;
                },
            ],
        ]);

        $request = Request::create('/items');
        $this->app->instance('request', $request);
        $configured = $this->respond((new EnvelopeResource(['id' => 1]))->toResponse($request));
        $overridden = $this->respond(
            (new EnvelopeResource(['id' => 2]))
                ->withStatus(202)
                ->additional(['request_id' => 'fixed'])
                ->toResponse($request)
        );

        $this->respond($this->responder()->success())->assertStatus(201);
        $configured->assertStatus(201)->assertJsonPath('request_id', 1);
        $overridden->assertStatus(202)->assertJsonPath('request_id', 'fixed');
        $this->assertSame(2, $calls);
    }

    private function responder(): Responder
    {
        return $this->app->make(Responder::class);
    }

    private function respond(JsonResponse $response): TestResponse
    {
        return TestResponse::fromBaseResponse($response);
    }
}
