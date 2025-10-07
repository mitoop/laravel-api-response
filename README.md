<h1 align="center">Laravel API Response</h1>

## 主要功能
- 🎯  统一标准化返回格式
- 🔧  支持自定义状态码和扩展字段
- 📦  支持自定义响应头
- 🚀  完美兼容 Laravel API 资源
- 🛑  统一异常处理格式

## 环境需求
以下为最低环境要求：
- PHP >= 8.2
- Laravel ^11.0｜^12.0

## 安装

```shell
composer require mitoop/laravel-api-response
```

## 输出格式
```jsonc
{
  "code": 0,                   // 状态码，默认成功0，失败1，登录失效-1
  "message": "success",        // 提示信息
  "data": {},                  // 主体内容
  "meta": {                    // 分页信息，普通响应返回 {}，分页响应返回分页详情
    "pagination": "page",      // 分页类型: page 或 cursor
    "page": 1,                 // 当前页码（page分页）
    "page_size": 20,           // 每页条数
    "has_more": false,         // 是否有下一页
    "total": 100,              // 总条数（仅 paginate）
    "next_cursor": "..."       // 下一个游标（仅 cursor 分页）
  },
  "errors": {}                  // 错误信息对象（数组或对象）
}
```

```text
code       : 状态码，默认值为 0（成功）、1（失败）、-1（登录失效），可通过 `setDefaults` 修改
message    : 提示信息
data       : 成功响应的主体内容
meta       : 分页信息，普通响应返回 {}，分页响应返回分页详情
  meta.pagination : 分页类型，可为 "page" 或 "cursor"
  meta.page       : 当前页码（仅 page 分页）
  meta.page_size  : 每页条数
  meta.has_more   : 是否有下一页
  meta.total      : 总条数（仅 paginate 方法有，simplePaginate 没有）
  meta.next_cursor: 下一个游标（仅 cursor 分页）
errors      : 错误信息对象(数组)，默认为空对象 {}
```

## 使用

```php
use Mitoop\Http\RespondsWithJson;

class Controller extends BaseController
{
    use RespondsWithJson;
}
```

#### 可用方法

包含三个方法 `success`, `error`, `deny`, 分别对应成功, 失败, 登录失效三种情况.

```php
class Controller extends BaseController
{
    use RespondsWithJson;

    public function one()
    {
       return $this->success();
    }

    public function two()
    {
       return $this->success(['Hello']);
    }

    public function three()
    {
       return $this->success(User::active()->paginate());
    }

    public function four()
    {
       return $this->error();
    }

    public function five()
    {
       return $this->error('自定义错误信息');
    }

    public function six()
    {
       return $this->deny('登录信息已失效, 请重新登陆!');
    }
}
```

## 自定义状态码以及扩展字段

在 `AppServiceProvider@boot` 方法中添加如下代码

```php
use Mitoop\Http\JsonResponderDefault;

app(JsonResponderDefault::class)->apply([
    'success' => 0,
    'error' => 1,
    'deny' => -1,
    'extra' => [
       'request_id' => app('request_id'),
    ],
]);
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
    public function show()
    {
       return new LoraResource(Lora::find(1));
    }
}
```

## 异常

通过 `JsonExceptionRenderer` 统一处理异常输出格式.

```php
use Illuminate\Foundation\Configuration\Exceptions;

->withExceptions(function (Exceptions $exceptions) {
    $exceptions->dontReport([
        ClientSafeException::class,
    ]);
    
    // 特殊异常映射
    $exceptions->map([
        JWTException::class => fn ($e) => new AuthenticationException
    ]);
    
    // 其他异常将由统一的 Handler 接管，不需要单独处理
})
```

## License

MIT
