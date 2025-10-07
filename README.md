<h1 align="center">Laravel API Response</h1>

## ✨ 主要功能
🎯 提供统一且标准化的响应格式，同时支持普通分页与游标分页  
🔧 支持自定义状态码与扩展字段  
📦 灵活设置响应头（可添加签名、追踪 ID、版本号等）  
🚀 无缝兼容 Laravel API 资源  
🐛 已定制异常处理器，实现统一格式化输出  

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

包含三个方法 `success`, `error`, `deny`, 分别对应成功, 失败, 登录失效三种情况

```php
class Controller extends BaseController
{
    use RespondsWithJson;

    public function successEmpty()
    {
       return $this->success();
    }

    public function successWithData()
    {
       return $this->success(['Hello']);
    }

    public function successWithPagination()
    {
       return $this->success(User::active()->paginate());
    }

    public function errorDefault()
    {
       return $this->error();
    }

    public function errorCustomMessage()
    {
       return $this->error('自定义错误信息');
    }

    public function unauthorized()
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
支持 API 资源, `只需要`改下继承关系, 其他不需要任何改变  
Tips: 更改下系统默认的 `stub`, 每次直接生成好继承关系

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
use Mitoop\Http\Resources\ResourceCollection;

class LoraCollection extends ResourceCollection
{

}
```

#### 和原来一样，直接返回资源对象，自动应用统一响应格式

```php
class Controller extends BaseController
{
    public function show()
    {
       // 直接返回标准化的 API Resource
       return new LoraResource(Lora::find(1));
    }
}
```

## 异常
已定制异常处理器，开发者只需少量配置即可实现统一格式化输出

```php
use Illuminate\Foundation\Configuration\Exceptions;

->withExceptions(function (Exceptions $exceptions) {
    $exceptions->dontReport([
        ClientSafeException::class,
    ]);
    
    // 只需要根据需求配置异常映射
    // 其他异常由统一 Handler 接管，无需单独处理
    /** @noinspection PhpParamsInspection */
    $exceptions->map([
        JWTException::class => fn ($e) => new AuthenticationException
    ]); 
})
```

## License

MIT
