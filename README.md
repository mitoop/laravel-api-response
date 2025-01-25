<h1 align="center">API Response Trait</h1>

<p align="center">🍎 定制统一的输出格式</p>

## 环境需求

- PHP >= 8.2
- Laravel ^11.0

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
code: 状态码 默认成功为0, 失败为1, 登录失效为-1, 可以通过 `setDefaults` 方法修改
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
use Mitoop\Http\JsonResponder;

class Controller extends BaseController
{
    use JsonResponder;
}
```

#### 可用方法

包含方法 `success`, `error`, `reject` 三个方法, 分别对应成功, 失败, 登录失效三种情况.

```php
class Controller extends BaseController
{
    use JsonResponder;

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
       return $this->reject('登录信息已失效, 请重新登陆!');
    }
}
```

## 自定义状态码以及扩展字段
在 `AppServiceProvider@boot` 方法中添加如下代码

```php
use Mitoop\Http\Config;

app(Config::class)->setDefaults([
    'success' => 0,
    'error' => 1,
    'reject' => -1,
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

通过 `render` 方法统一处理异常输出格式.

```php
->withExceptions(function (Exceptions $exceptions) {
    $exceptions->render(function (NotFoundHttpException $e, Request $request) {
        return app(Responder::class)->error('未找到对应数据');
    });
})
```


## License

MIT
