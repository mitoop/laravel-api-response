<h1 align="center">Laravel API Response</h1>

<p align="center">为 Laravel API 提供统一的 JSON 响应格式。</p>

## ✨ 主要功能

🎯 统一 `success`、`code`、`message`、`data` 结构，并支持页码分页与游标分页  
🔧 业务码、提示文案、HTTP 状态和扩展字段都可以配置  
📦 响应头可按响应体生成，适合放签名、追踪 ID 或版本号  
🚀 直接返回 API Resource 时，自动套用同一份格式

## 环境需求

- PHP 8.2 及以上
- Laravel 11、12 或 13

## 安装

```shell
composer require mitoop/laravel-api-response
```

## 响应格式

成功时返回 `success`、`code`、`message`、`data`。分页响应才有 `meta`，失败响应才有 `errors`。

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

`data` 没有内容时是 `{}`，列表是数组。

业务码放在 `code`。HTTP 状态单独设置，`success()`、`error()`、`deny()` 默认都是 `200`。

### 分页

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

`simplePaginate()` 没有 `total`。

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

### 失败

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

## 在控制器中使用

```php
use Mitoop\Http\RespondsWithJson;

class Controller extends BaseController
{
    use RespondsWithJson;

    public function index()
    {
        return $this->success(User::query()->paginate());
    }

    public function empty()
    {
        return $this->success();
    }

    public function invalid()
    {
        return $this->error('名称必填', errors: ['name' => ['名称必填']], status: 422);
    }

    public function unauthorized()
    {
        return $this->deny('登录信息已失效，请重新登录', status: 401);
    }
}
```

`success()`、`error()`、`deny()` 分别表示成功、失败和登录失效。`status` 只改变 HTTP 状态，不改变 `code`。

游标分页把 `paginate()` 换成 `cursorPaginate()` 即可。

## 配置

在 `AppServiceProvider::boot()` 里调用 `apply()`。没写的项目保持默认值。

| 配置 | 默认值 |
| --- | --- |
| `success` / `error` / `deny` | 业务码 `0` / `1` / `-1` |
| `message.success` / `message.error` / `message.deny` | `ok` / `error` / `unauthorized` |
| `http_status.success` / `http_status.error` / `http_status.deny` | HTTP 状态 `200` / `200` / `200` |

```php
use Mitoop\Http\ResponderConfig;

app(ResponderConfig::class)->apply([
    'http_status' => [
        'deny' => 401,
    ],
    'extra' => [
        'request_id' => fn () => app('request_id'),
    ],
]);
```

`extra` 会合并到每次响应的最外层。值可以是闭包，闭包在每次响应时执行。`success`、`code`、`message`、`data`、`meta`、`errors` 是保留字段，写在 `extra` 里会被忽略。

## API 资源

资源类改掉父类后，直接返回即可。字段、分页信息和响应头与 `success()` 一致。

```php
use Illuminate\Http\Request;
use Mitoop\Http\Resources\Resource;

class UserResource extends Resource
{
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
use Mitoop\Http\Resources\ResourceCollection;

class UserCollection extends ResourceCollection
{
}
```

```php
public function show(User $user)
{
    return (new UserResource($user))
        ->withMessage('创建成功')
        ->withStatus(201);
}

public function index()
{
    return new UserCollection(User::query()->paginate());
}
```

`withMessage()` 修改这次响应的 `message`。`withStatus()` 修改这次响应的 HTTP 状态。

执行 `php artisan stub:publish` 后，把 `stubs/resource.stub` 的父类改成 `Mitoop\Http\Resources\Resource`，把 `stubs/resource-collection.stub` 的父类改成 `Mitoop\Http\Resources\ResourceCollection`。之后 `make:resource` 会直接生成对应的继承关系。

## 响应头

实现 `Mitoop\Http\Headers\HeaderResolverInterface`，在服务提供者的 `register()` 里替换默认绑定。控制器响应和 API Resource 都会带上这些响应头。

用传入的 `$jsonOptions` 编码 payload，结果与响应体一致，可以直接用来签名。

```php
use Mitoop\Http\Headers\HeaderResolverInterface;

class SignatureHeader implements HeaderResolverInterface
{
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

## 异常

异常仍由应用自己处理。需要统一成上面的 JSON 格式时，在 `bootstrap/app.php` 里调用 `Responder`。返回 `null` 的请求继续走 Laravel 原来的处理。

```php
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Mitoop\Http\Responder;

->withExceptions(function (Exceptions $exceptions) {
    $exceptions->render(function (AuthenticationException $e, Request $request) {
        if (! $request->expectsJson()) {
            return null;
        }

        return app(Responder::class)->deny('登录信息已失效，请重新登录');
    });
});
```

## License

MIT
