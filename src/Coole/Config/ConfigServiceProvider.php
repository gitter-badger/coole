<?php

/*
 * This file is part of the guanguans/coole.
 *
 * (c) guanguans <ityaozm@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled.
 */

namespace Guanguans\Coole\Config;

use Guanguans\Coole\Able\BootAbleProviderInterface;
use Guanguans\Coole\App;
use Guanguans\Di\Container;
use Guanguans\Di\ServiceProviderInterface;

class ConfigServiceProvider implements ServiceProviderInterface, BootAbleProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public function boot(App $app)
    {
        // TODO: Implement boot() method.
    }

    /**
     * {@inheritdoc}
     */
    public function register(Container $app)
    {
        $app->singleton(Config::class, function ($app) {
            return new Config();
        });
        $app->alias(Config::class, 'config');
    }
}
