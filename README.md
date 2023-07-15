# Laravel api response

## Exception
```php
   // App\Exceptions\Handler@register
   $this->renderable(function (Throwable $e, Request $request) {
            if ($e instanceof ClientSafeException) {
                return $this->error($e->getMessage());
            }

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
```