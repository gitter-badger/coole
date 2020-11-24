<?php

declare(strict_types=1);

/*
 * This file is part of the guanguans/coole.
 *
 * (c) guanguans <ityaozm@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled.
 */

namespace Guanguans\Coole\Controller;

use Guanguans\Coole\Exception\InvalidClassException;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ControllerResolverInterface;

/**
 * This file is modified from `Symfony\Component\HttpKernel\Controller\ControllerResolver`.
 *
 * @see https://github.com/symfony/http-kernel
 */
class ControllerResolver implements ControllerResolverInterface
{
    /**
     * @var \Psr\Log\LoggerInterface|null
     */
    private $logger;

    public function __construct(LoggerInterface $logger = null)
    {
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function getController(Request $request)
    {
        if (! $controller = $request->attributes->get('_controller')) {
            if (null !== $this->logger) {
                $this->logger->warning('Unable to look for the controller as the "_controller" parameter is missing.');
            }

            return false;
        }

        if (\is_array($controller)) {
            if (isset($controller[0]) && \is_string($controller[0]) && isset($controller[1])) {
                try {
                    $controller[0] = $this->instantiateController($controller[0]);
                    if (! $controller[0] instanceof Controller) {
                        throw new InvalidClassException(sprintf('The controller must be implements "%s" .', Controller::class));
                    }
                } catch (\Error | \LogicException $e) {
                    try {
                        // We cannot just check is_callable but have to use reflection because a non-static method
                        // can still be called statically in PHP but we don't want that. This is deprecated in PHP 7, so we
                        // could simplify this with PHP 8.
                        if ((new \ReflectionMethod($controller[0], $controller[1]))->isStatic()) {
                            return $controller;
                        }
                    } catch (\ReflectionException $reflectionException) {
                        throw $e;
                    }

                    throw $e;
                }
            }

            if (! \is_callable($controller)) {
                throw new \InvalidArgumentException(sprintf('The controller for URI "%s" is not callable: ', $request->getPathInfo()).$this->getControllerError($controller));
            }

            return $controller;
        }

        if (\is_object($controller)) {
            if (! \is_callable($controller)) {
                throw new \InvalidArgumentException(sprintf('The controller for URI "%s" is not callable: ', $request->getPathInfo()).$this->getControllerError($controller));
            }

            return $controller;
        }

        if (\function_exists($controller)) {
            return $controller;
        }

        try {
            $callable = $this->createController($controller);
        } catch (\InvalidArgumentException $e) {
            throw new \InvalidArgumentException(sprintf('The controller for URI "%s" is not callable: ', $request->getPathInfo()).$e->getMessage(), 0, $e);
        }

        if (! \is_callable($callable)) {
            throw new \InvalidArgumentException(sprintf('The controller for URI "%s" is not callable: ', $request->getPathInfo()).$this->getControllerError($callable));
        }

        return $callable;
    }

    /**
     * 返回给定控制器的可调用对象
     *
     * @return callable A PHP callable
     *
     * @throws \InvalidArgumentException When the controller cannot be created
     */
    protected function createController(string $controller)
    {
        if (false === strpos($controller, '::')) {
            $controller = $this->instantiateController($controller);

            if (! \is_callable($controller)) {
                throw new \InvalidArgumentException($this->getControllerError($controller));
            }

            return $controller;
        }

        [$class, $method] = explode('::', $controller, 2);

        try {
            $controller = [$this->instantiateController($class), $method];
        } catch (\Error | \LogicException $e) {
            try {
                if ((new \ReflectionMethod($class, $method))->isStatic()) {
                    return $class.'::'.$method;
                }
            } catch (\ReflectionException $reflectionException) {
                throw $e;
            }

            throw $e;
        }

        if (! \is_callable($controller)) {
            throw new \InvalidArgumentException($this->getControllerError($controller));
        }

        return $controller;
    }

    protected function instantiateController(string $class): ControllerResolverInterface
    {
        return app($class);
    }

    private function getControllerError($callable): string
    {
        if (\is_string($callable)) {
            if (false !== strpos($callable, '::')) {
                $callable = explode('::', $callable, 2);
            } else {
                return sprintf('Function "%s" does not exist.', $callable);
            }
        }

        if (\is_object($callable)) {
            $availableMethods = $this->getClassMethodsWithoutMagicMethods($callable);
            $alternativeMsg = $availableMethods ? sprintf(' or use one of the available methods: "%s"', implode('", "', $availableMethods)) : '';

            return sprintf('Controller class "%s" cannot be called without a method name. You need to implement "__invoke"%s.', get_debug_type($callable), $alternativeMsg);
        }

        if (! \is_array($callable)) {
            return sprintf('Invalid type for controller given, expected string, array or object, got "%s".', get_debug_type($callable));
        }

        if (! isset($callable[0]) || ! isset($callable[1]) || 2 !== \count($callable)) {
            return 'Invalid array callable, expected [controller, method].';
        }

        [$controller, $method] = $callable;

        if (\is_string($controller) && ! class_exists($controller)) {
            return sprintf('Class "%s" does not exist.', $controller);
        }

        $className = \is_object($controller) ? get_debug_type($controller) : $controller;

        if (method_exists($controller, $method)) {
            return sprintf('Method "%s" on class "%s" should be public and non-abstract.', $method, $className);
        }

        $collection = $this->getClassMethodsWithoutMagicMethods($controller);

        $alternatives = [];

        foreach ($collection as $item) {
            $lev = levenshtein($method, $item);

            if ($lev <= \strlen($method) / 3 || false !== strpos($item, $method)) {
                $alternatives[] = $item;
            }
        }

        asort($alternatives);

        $message = sprintf('Expected method "%s" on class "%s"', $method, $className);

        if (\count($alternatives) > 0) {
            $message .= sprintf(', did you mean "%s"?', implode('", "', $alternatives));
        } else {
            $message .= sprintf('. Available methods: "%s".', implode('", "', $collection));
        }

        return $message;
    }

    private function getClassMethodsWithoutMagicMethods($classOrObject): array
    {
        $methods = get_class_methods($classOrObject);

        return array_filter($methods, function (string $method) {
            return 0 !== strncmp($method, '__', 2);
        });
    }
}
