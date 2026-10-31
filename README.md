# Laravel API Response

Laravel API Response 为接口提供统一的 JSON 结构。成功、失败和 API Resource 使用同一组字段：`success`、`code`、`message` 和 `data`。分页响应额外包含 `meta`，失败响应额外包含 `errors`。

## 安装

通过 Composer 安装：

```shell
composer require mitoop/laravel-api-response
```

要求 PHP 8.2 及以上，以及 Laravel 11、12 或 13。

## 配置

安装后即可使用，不必发布配置文件。默认值如下：

| 配置 | 默认值 |
| --- | --- |
| `success` / `error` / `deny` | 业务码 `0` / `1` / `-1` |
| `message.success` / `message.error` / `message.deny` | `ok` / `error` / `unauthorized` |
| `http_status.success` / `http_status.error` / `http_status.deny` | HTTP 状态 `200` / `200` / `200` |

如果需要修改默认值，在 `AppServiceProvider` 的 `boot` 方法中调用 `apply`。未出现的配置项保持默认值：

```php
use Mitoop\Http\ResponderConfig;

public function boot(): void
{
    app(ResponderConfig::class)->apply([
        'http_status' => [
            'deny' => 401,
        ],
        'extra' => [
            'request_id' => fn () => (string) request()->header('X-Request-Id'),
        ],
    ]);
}
```

`extra` 会合并到每次响应的最外层。值可以是闭包，闭包在生成响应时执行，因此适合放入请求级数据。如果某次响应已经提供了同名字段，对应闭包不会执行。

`success`、`code`、`message`、`data`、`meta` 和 `errors` 是保留字段。写在 `extra` 中的同名字段会被忽略。

## 响应格式

成功响应：

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

`data` 没有内容时为 JSON 对象 `{}`，列表为数组。业务码放在 `code` 中。HTTP 状态单独设置，默认均为 `200`。

分页信息放在与 `data` 同级的 `meta` 中。页码分页：

```json
{
  "pagination": "page",
  "page": 1,
  "page_size": 20,
  "has_more": false,
  "total": 100
}
```

`simplePaginate` 不返回 `total`。游标分页：

```json
{
  "pagination": "cursor",
  "page_size": 20,
  "has_more": true,
  "next_cursor": "eyJpZCI6MX0"
}
```

没有下一页时，`next_cursor` 为 `null`。

失败响应：

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

在控制器中引入 `RespondsWithJson`。`success`、`error` 和 `deny` 分别表示成功、失败和登录失效：

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

`status` 参数只改变 HTTP 状态，不改变 `code`。省略数据时，直接调用 `success`：

```php
return $this->success();
```

游标分页将 `paginate` 替换为 `cursorPaginate`：

```php
return $this->success(User::query()->cursorPaginate());
```

## API 资源

让资源类继承本包提供的基类，然后像平时一样返回资源。响应字段、分页信息和响应头与 `success` 一致。未调用 `withStatus` 时，HTTP 状态使用 `http_status.success`。

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

如果只想修改这一次响应的文案或 HTTP 状态，可以链式调用 `withMessage` 和 `withStatus`。单条资源和集合都支持：

```php
return (new UserResource($user))
    ->withMessage('创建成功')
    ->withStatus(201);

return (new UserCollection(User::query()->paginate()))
    ->withMessage('获取成功');
```

执行 `php artisan stub:publish` 后，将 `stubs/resource.stub` 的父类改为 `Mitoop\Http\Resources\Resource`，将 `stubs/resource-collection.stub` 的父类改为 `Mitoop\Http\Resources\ResourceCollection`。之后 `make:resource` 会生成对应的继承关系。

## 响应头

实现 `HeaderResolverInterface`，并在服务提供者的 `register` 方法中替换默认绑定。控制器返回的响应和 API Resource 都会带上这些响应头。

使用参数中的 `$jsonOptions` 编码 payload，得到的字符串与响应体一致，因此可以直接用于签名：

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

框架的异常处理保持不变。如果希望接口异常也使用上述 JSON 结构，在应用中添加一个可调用的 `ApiExceptionRenderer`：

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

不是 JSON 请求时返回 `false`，Laravel 会继续使用原来的异常页面或重定向。

然后在 `bootstrap/app.php` 中注册。应用自己的业务异常可以用同样的方式加入 `match`：

```php
use App\Exceptions\ApiExceptionRenderer;
use Illuminate\Http\Request;

->withExceptions(function (Exceptions $exceptions) {
    $exceptions->render(fn (Throwable $e, Request $request) => app(ApiExceptionRenderer::class)($e, $request));
});
```

## License

MIT
