# 事件

> 事件调度器由 [symfony/event-dispatcher](https://github.com/symfony/event-dispatcher) 提供支持。

## 编写事件

``` php
<?php

declare(strict_types=1);

/*
 * This file is part of the coolephp/skeleton.
 *
 * (c) guanguans <ityaozm@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled.
 */

namespace App\Event;

use Guanguans\Coole\Event\Event;

class ExampleEvent extends Event
{
    const  NAME = 'example';
}
```

## 编写监听

``` php
<?php

declare(strict_types=1);

/*
 * This file is part of the coolephp/skeleton.
 *
 * (c) guanguans <ityaozm@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled.
 */

namespace App\Listener;

use Guanguans\Coole\Event\Event;
use Guanguans\Coole\Event\ListenerInterface;

class ExampleListener implements ListenerInterface
{
    public function handle(Event $event)
    {
        // To do something.
    }
}

```

## 绑定事件

默认在 `config/event.php` 文件中完成绑定配置。

``` php
<?php

declare(strict_types=1);

/*
 * This file is part of the coolephp/skeleton.
 *
 * (c) guanguans <ityaozm@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled.
 */

return [
    'listener' => [
        \App\Event\ExampleEvent::class => [
            \App\Listener\ExampleListener::class
        ]
    ],
];
```

## 触发事件

``` php
// 触发事件
event(new \App\Event\ExampleEvent());

// 绑定事件并且触发事件
event(new \App\Event\ExampleEvent(), \App\Listener\ExampleListener::class);
event(new \App\Event\ExampleEvent(), function (){
    dump('This is a testing.');
});
```
