<h1 align="center">API Response Trait</h1>

<p align="center">🍎 定制统一的输出格式</p>


```shell
echo $a;

```
## 环境需求

- PHP >= 8.1
- Laravel ^10.0

## 安装

```shell
composer require mitoop/laravel-api-response
```

## 输出格式
#### 普通格式
```json
{
  "code": 0,
  "message": "success",
  "data": null
}
```

#### 分页格式
```json
{
  "code": 0,
  "message": "success",
  "data": [
    {
      "id": 1,
      "nickname": "admin",
      "email": "i@admin.com",
      "last_login": "2023-07-29 12:49:16",
      "status": true
    }
  ],
  "meta": {
    "page": 1,
    "page_size": 20,
    "has_more": false,
    "total": 1
  }
}
```

```text
code: 状态码 成功为0/失败为1/Token失效为-1, 前端可以作为逻辑判断依据
message: 提示信息
data: 内容主体
meta: 分页信息
meta.page: 当前分页
meta.page_size: 分页大小
meta.has_more: 是否还有下一页
meta.total: 总数 `paginate` 方法有 total 属性, `simplePaginate` 方法没有
```

## 使用
```php
use Mitoop\Http\ResponseTrait;

class Controller extends BaseController
{
    // 引用 `Trait` 即可
    use ResponseTrait;
}
```

#### 可用方法

包含方法 `success`, `error`, `unauthenticatedError` 三个方法,

`unauthenticatedError` 是一个特殊(code=-1)的 error 方法.

```php
class Controller extends BaseController
{
    use ResponseTrait;

    public function demo1()
    {
       return $this->success();
    }

    public function demo2()
    {
       return $this->success(['Hello']);
    }

    public function demo3()
    {
       // success 方法支持直接传入分页对象
       return $this->success(User::active()->paginate());
    }

    public function demo4()
    {
       return $this->error();
    }

    public function demo5()
    {
       return $this->error('自定义错误信息');
    }

    public function demo5()
    {
       return $this->unauthenticatedError('登录信息已失效, 请重新登陆!');
    }
}
```

## API 资源

支持 API 资源, `只需要`改下继承关系, 其他不需要任何改变.

Tips: 更改下系统默认的 `stub`, 每次直接生成好继承关系.

#### 普通资源继承 `Mitoop\Http\Resources\Resource`
```php
use Illuminate\Http\Request;
use Mitoop\Http\Resources\Resource;

class LoraResource extends Resource
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

#### 资源集合继承 `Mitoop\Http\Resources\ResourceCollection`
```php
use Mitoop\Http\Resources\Resource;

namespace App\Http\Resources\User;

use Mitoop\Http\Resources\ResourceCollection;

class LoraCollection extends ResourceCollection
{

}
```

#### 和原来一样直接返回
```php
class Controller extends BaseController
{
    public function demo1()
    {
       return new LoraResource(Lora::find(1));
    }
}
```

## 异常

通过 `renderable` 方法统一处理异常输出格式.

```php
namespace App\Exceptions;

class Handler extends ExceptionHandler
{
    use ResponseTrait;

    public function register(): void
    {
        $this->renderable(function (Throwable $e, Request $request) {
            if ($e instanceof NotFoundHttpException) {
                return $this->error('未找到对应数据');
            }

            if ($e instanceof AccessDeniedHttpException) {
                return $this->error('您没有该权限');
            }

            if ($e instanceof AuthenticationException) {
                return $this->unauthenticatedError();
            }

            if ($e instanceof JWTException) {
                return $this->unauthenticatedError();
            }

            if ($e instanceof ValidationException) {
                return $this->error(Arr::first(Arr::flatten($e->errors())), $e->errors());
            }

            $message = config('app.debug') ? $e->getMessage() : '服务器发生了未知的异常，请稍后再试';
            $data = config('app.debug') ? format_exception($e, [
                'url' => sprintf('[%s] %s', request()->method(), request()->url()),
                'trace' => collect($e->getTrace())->map(function ($trace) {
                    $trace = Arr::except($trace, ['args']);

                    if (isset($trace['file'])) {
                        $trace['file'] = str_replace(base_path(), '', $trace['file']);
                    }

                    return $trace;
                })->all(),
            ]) : null;

            if (empty($message)) {
                $message = '服务器发生了未知的异常，请稍后再试';
            }

            return $this->error($message, $data);
        });
    }
}
```


## License

MIT
