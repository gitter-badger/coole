<?php

declare(strict_types=1);

/**
 * This file is part of Coole.
 *
 * @link     https://github.com/guanguans/coole
 * @contact  guanguans <ityaozm@gmail.com>
 * @license  https://github.com/guanguans/coole/blob/main/LICENSE
 */

namespace Coole\Config;

use Coole\Foundation\Able\AfterRegisterAbleProviderInterface;
use Coole\Foundation\Able\BeforeRegisterAbleProviderInterface;
use Coole\Foundation\App;
use Guanguans\Di\Container;
use Guanguans\Di\ServiceProviderInterface;
use Tightenco\Collect\Support\Collection as Config;

class ConfigServiceProvider implements ServiceProviderInterface, AfterRegisterAbleProviderInterface, BeforeRegisterAbleProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public function beforeRegister(App $app)
    {
        if (null !== $app['env_path']) {
            $app->loadEnv($app['env_path']);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function register(Container $app)
    {
        $app->singleton('config', function ($app) {
            return new Config();
        });
    }

    /**
     * {@inheritdoc}
     */
    public function afterRegister(App $app)
    {
        if (null !== $app['config_path']) {
            $app->loadConfig($app['config_path']);
        }
    }
}