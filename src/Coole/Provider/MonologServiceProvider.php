<?php

declare(strict_types=1);

/*
 * This file is part of the guanguans/coole.
 *
 * (c) guanguans <ityaozm@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled.
 */

namespace Guanguans\Coole\Provider;

use Guanguans\Coole\Able\BeforeRegisterAbleProviderInterface;
use Guanguans\Coole\Able\BootAbleProviderInterface;
use Guanguans\Coole\Able\EventListenerAbleProviderInterface;
use Guanguans\Coole\App;
use Guanguans\Coole\Listener\LogListener;
use Guanguans\Di\Container;
use Guanguans\Di\ServiceProviderInterface;
use Monolog\ErrorHandler;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\GroupHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class MonologServiceProvider implements ServiceProviderInterface, BootAbleProviderInterface, EventListenerAbleProviderInterface, BeforeRegisterAbleProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public function beforeRegister(App $app)
    {
        $app->addConfig([
            'logger' => [
                'name' => 'app',
                'level' => Logger::DEBUG,
                'bubble' => true,
                'file_permission' => null,
                'log_file' => null,
                'use_locking' => false,
                'formatter' => [
                    'format' => null,
                    'date_format' => 'Y-m-d H:i:s',
                    'allow_inline_Line_Breaks' => false,
                    'ignore_empty_context_and_extra' => false,
                ],
            ],
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function register(Container $app)
    {
        $app->singleton('monolog', function ($app) {
            $log = new Logger($app['config']['logger']['name']);
            $handler = new GroupHandler($app['monolog.handlers']);
            $log->pushHandler($handler);

            return $log;
        });
        $app->alias('monolog', 'logger');

        $app->singleton(LineFormatter::class, function ($app) {
            return new LineFormatter(
                $app['config']['logger']['formatter']['format'] ?? null,
                $app['config']['logger']['formatter']['date_format'] ?? null,
                $app['config']['logger']['formatter']['allow_inline_Line_Breaks'] ?? false,
                $app['config']['logger']['formatter']['ignore_empty_context_and_extra'] ?? false
            );
        });

        $app->alias(LineFormatter::class, 'monolog.formatter');

        $app->singleton(StreamHandler::class, function ($app) {
            $handler = new StreamHandler(
                $app['config']['logger']['log_file'],
                $app['config']['logger']['level'] ?? Logger::DEBUG,
                $app['config']['logger']['bubble'] ?? true,
                $app['config']['logger']['file_permission'] ?? null,
                $app['config']['logger']['use_locking'] ?? false
            );
            $handler->setFormatter($app['monolog.formatter']);

            return $handler;
        });
        $app->alias(StreamHandler::class, 'monolog.handler');

        $app->singleton('monolog.handlers', function ($app) {
            $handlers = [];
            if ($app['config']['logger']['log_file']) {
                $handlers[] = $app['monolog.handler'];
            }

            return $handlers;
        });

        $app->singleton(LogListener::class, function ($app) {
            return new LogListener($app['logger']);
        });
        $app->alias(LogListener::class, 'monolog.listener');
    }

    /**
     * {@inheritdoc}
     */
    public function subscribe(App $app, EventDispatcherInterface $dispatcher)
    {
        if ($app->has('monolog.listener')) {
            $dispatcher->addSubscriber($app['monolog.listener']);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function boot(App $app)
    {
        if ($app['debug']) {
            ErrorHandler::register($app['monolog']);
        }
    }
}
