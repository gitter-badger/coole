# 控制器

Coole 的控制器解析器没有使用 [symfony/http-kernel](https://github.com/symfony/http-kernel) 默认的控制器解析器，而是自己实现了 `Symfony\Component\HttpKernel\Controller\ControllerResolverInterface` 接口，控制器构造方法实现了依赖注入的功能。

## 编写控制器

``` php
<?php

namespace App\Controller;

use Guanguans\Coole\Controller\Controller;

class IndexController extends Controller
{
    protected $middleware = [
        App\Middlewa\DemoMiddleware::class
    ];
    
    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    public function hello($hello)
    {
        return sprintf('Hello %s', $hello);
    }
}
```
