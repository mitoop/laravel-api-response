<h2 align="center">🍎 为 Laravel API 提供统一的 JSON 响应格式</h2>

```json
{
  "success": true,
  "code": 0,
  "message": "ok",
  "data": [
    {
      "id": 1,
      "name": "Ada"
    }
  ],
  "meta": {
    "pagination": "page",
    "page": 1,
    "page_size": 20,
    "has_more": false,
    "total": 100
  }
}
```

- 支持成功、失败和登录失效
- 支持页码分页和游标分页
- 几乎零改动即可支持 API Resource
- 支持自定义响应头
- 接口异常也可以按同一套格式返回

## 环境要求

- PHP ^8.2
- Laravel ^11.0 | ^12.0 | ^13.0

## 安装

通过 Composer 安装：

```shell
composer require mitoop/laravel-api-response
```

## 配置

安装后即可使用。默认值如下：

| 配置                                                             | 默认值                          |
| ---------------------------------------------------------------- | ------------------------------- |
| `success` / `error` / `deny`                                     | 业务码 `0` / `1` / `-1`         |
| `message.success` / `message.error` / `message.deny`             | `ok` / `error` / `unauthorized` |
| `http_status.success` / `http_status.error` / `http_status.deny` | HTTP 状态 `200` / `200` / `200` |

要改默认值，在 `AppServiceProvider` 的 `boot` 里调用 `apply`。没传入的项保持默认值：

```php
use Illuminate\Support\Str;
use Mitoop\Http\ResponderConfig;

public function boot(): void
{
    app(ResponderConfig::class)->apply([
        'http_status' => [
            'deny' => 401,
        ],
        'extra' => [
            'request_id' => fn () => (string) Str::uuid7(),
        ],
    ]);
}
```

`extra` 合并到每次响应的最外层。值可以是闭包，生成响应时才执行。上面的 `request_id` 由应用自己生成。某次响应已经带了同名字段时，对应闭包不会执行。

`success`、`code`、`message`、`data`、`meta`、`errors` 是保留字段。`extra` 里的同名字段会被忽略。

## 响应格式

**成功响应：**

```json
{
  "success": true,
  "code": 0,
  "message": "ok",
  "data": {
    "id": 1,
    "name": "Ada"
  }
}
```

`data` 为空时是 JSON 对象 `{}`，列表是数组。业务码在 `code`。HTTP 状态单独设置，默认都是 `200`。

分页信息在 `meta`，与 `data` 同级。

页码分页：

```json
{
  "pagination": "page",
  "page": 1,
  "page_size": 20,
  "has_more": false,
  "total": 100
}
```

`simplePaginate` 不带 `total`。

游标分页：

```json
{
  "pagination": "cursor",
  "page_size": 20,
  "has_more": true,
  "next_cursor": "eyJpZCI6MX0"
}
```

没有下一页时，`next_cursor` 为 `null`。

**失败响应：**

```json
{
  "success": false,
  "code": 1,
  "message": "名称必填",
  "data": {},
  "errors": {
    "name": ["名称必填"]
  }
}
```

## 使用

在控制器中引入 `RespondsWithJson`。`success` 表示成功，`error` 表示失败，`deny` 表示未授权：

```php
<?php

namespace App\Http\Controllers;

use App\Models\User;
use Mitoop\Http\RespondsWithJson;

class UserController extends Controller
{
    use RespondsWithJson;

    public function index()
    {
        return $this->success(User::query()->paginate());
    }

    public function store()
    {
        return $this->error('名称必填', errors: [
            'name' => ['名称必填'],
        ], status: 422);
    }

    public function destroy()
    {
        return $this->deny('登录信息已失效，请重新登录');
    }
}
```

`status` 只设置 HTTP 状态，`code` 仍用配置里的业务码。不返回数据时直接调用 `success`：

```php
return $this->success();
```

游标分页把 `paginate` 换成 `cursorPaginate`：

```php
return $this->success(User::query()->cursorPaginate());
```

## API 资源

资源类只要改成继承本包的基类即可。使用方式不用变，还是直接返回资源，响应就会按统一结构输出。

```php
<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Mitoop\Http\Resources\Resource;

class UserResource extends Resource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
        ];
    }
}
```

```php
<?php

namespace App\Http\Resources;

use Mitoop\Http\Resources\ResourceCollection;

class UserCollection extends ResourceCollection
{
    //
}
```

```php
public function show(User $user)
{
    return new UserResource($user);
}

public function index()
{
    return new UserCollection(User::query()->paginate());
}
```

如果想更改本次响应的文案或 HTTP 状态，可以链式调用 `withMessage` 和 `withStatus`。单条资源和集合都支持：

```php
return (new UserResource($user))
    ->withMessage('创建成功')
    ->withStatus(201);

return (new UserCollection(User::query()->paginate()))
    ->withMessage('获取成功');
```

发布资源模板后，`make:resource` 会按本包的继承关系自动生成。执行 `php artisan stub:publish`，将 `stubs/resource.stub` 的父类改为 `Mitoop\Http\Resources\Resource`，将 `stubs/resource-collection.stub` 的父类改为 `Mitoop\Http\Resources\ResourceCollection`。

## 响应头（可选）

默认不加自定义响应头。需要附加响应头时，实现 `HeaderResolverInterface`，并在服务提供者的 `register` 里替换默认绑定，例如给响应报文加签名。控制器响应和 API Resource 都会带上这些头。

用参数里的 `$jsonOptions` 编码 payload，结果和响应体一致：

```php
<?php

namespace App\Http;

use Mitoop\Http\Headers\HeaderResolverInterface;

class SignatureHeader implements HeaderResolverInterface
{
    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, string>
     */
    public function resolve(array $payload, int $jsonOptions): array
    {
        return [
            'X-Signature' => hash('sha256', json_encode($payload, $jsonOptions)),
        ];
    }
}
```

```php
use App\Http\SignatureHeader;
use Mitoop\Http\Headers\HeaderResolverInterface;

public function register(): void
{
    $this->app->singleton(HeaderResolverInterface::class, SignatureHeader::class);
}
```

## 异常处理

框架自己的异常处理不变。接口异常也要这套 JSON 时，可以加一个 `ApiExceptionRenderer`：

```php
<?php

namespace App\Exceptions;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;
use Mitoop\Http\Responder;
use stdClass;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

class ApiExceptionRenderer
{
    public function __construct(private Responder $responder) {}

    public function __invoke(Throwable $e, Request $request): JsonResponse|false
    {
        if (! $request->expectsJson()) {
            return false;
        }

        return match (true) {
            $e instanceof AuthenticationException => $this->responder->deny('请先登录'),
            $e instanceof ValidationException => $this->responder->error(
                (string) Arr::first(Arr::flatten($e->errors())),
                errors: $e->errors(),
            ),
            $e instanceof NotFoundHttpException => $this->responder->error('没有找到对应内容'),
            default => $this->responder->error(
                '服务暂时不可用，请稍后再试',
                errors: app()->hasDebugModeEnabled() ? [
                    'exception' => $e::class,
                    'message' => $e->getMessage(),
                ] : new stdClass,
            ),
        };
    }
}
```

不是 JSON 请求时返回 `false`，Laravel 会继续使用原来的异常页面或重定向。也可以去掉这个判断，让所有请求都通过统一结构返回。按需要选择。

再到 `bootstrap/app.php` 注册。业务异常按同样方式加进 `match`：

```php
use App\Exceptions\ApiExceptionRenderer;
use Illuminate\Http\Request;

->withExceptions(function (Exceptions $exceptions) {
    $exceptions->render(fn (Throwable $e, Request $request) => app(ApiExceptionRenderer::class)($e, $request));
});
```

## License

MIT
