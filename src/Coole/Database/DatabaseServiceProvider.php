<?php

declare(strict_types=1);

/*
 * This file is part of the guanguans/coole.
 *
 * (c) guanguans <ityaozm@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled.
 */

namespace Guanguans\Coole\Database;

use Guanguans\Coole\Able\BeforeRegisterAbleProviderInterface;
use Guanguans\Coole\App;
use Guanguans\Di\Container;
use Guanguans\Di\ServiceProviderInterface;
use Illuminate\Container\Container as IlluminateContainer;
use Illuminate\Database\Capsule\Manager;
use Illuminate\Events\Dispatcher;

class DatabaseServiceProvider implements ServiceProviderInterface, BeforeRegisterAbleProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public function beforeRegister(App $app)
    {
        $app->addConfig([
            'database' => [
                'driver' => 'mysql',
                'connections' => [
                    'mysql' => [
                        'driver' => 'mysql',
                        'url' => '',
                        'host' => '127.0.0.1',
                        'port' => '3306',
                        'database' => 'coole',
                        'username' => 'coole',
                        'password' => '',
                        'unix_socket' => '',
                        'charset' => 'utf8mb4',
                        'collation' => 'utf8mb4_unicode_ci',
                        'prefix' => '',
                        'prefix_indexes' => true,
                        'strict' => true,
                        'engine' => null,
                        'options' => [],
                    ],
                ],
            ],
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function register(Container $app)
    {
        $app->singleton(Manager::class, function ($app) {
            $manager = new Manager();
            $manager->addConnection($app['config']['database']['connections'][$app['config']['database']['driver']]);

            // Set the event dispatcher used by Eloquent models... (optional)
            $manager->setEventDispatcher(new Dispatcher(new IlluminateContainer()));

            // Make this Capsule instance available globally via static methods... (optional)
            $manager->setAsGlobal();

            // Setup the Eloquent ORM... (optional; unless you've used setEventDispatcher())
            $manager->bootEloquent();

            return $manager;
        });

        $app->alias(Manager::class, 'database');
        $app->alias(Manager::class, 'db');
    }
}
