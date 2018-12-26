<?php

namespace Proxy;

class ClassNameResolver
{
    private const DECORATOR_SUFFIX = 'AutoDecorator';

    public function isDecoratorClassName(string $className): bool
    {
        return false !== strrpos($className, self::DECORATOR_SUFFIX);
    }

    public function resolve(string $originalClassName)
    {
        return $originalClassName . self::DECORATOR_SUFFIX;
    }
}
