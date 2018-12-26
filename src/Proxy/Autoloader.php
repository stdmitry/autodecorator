<?php

declare(strict_types=1);

namespace Proxy;

class Autoloader
{
    /**
     * @var FileNameResolver
     */
    private $fileNameResolver;

    /**
     * @var ClassNameResolver
     */
    private $classNameResolver;

    public function __construct(FileNameResolver $fileNameResolver, ClassNameResolver $classNameResolver)
    {
        $this->fileNameResolver = $fileNameResolver;
        $this->classNameResolver = $classNameResolver;
    }

    /**
     * {@inheritDoc}
     */
    public function __invoke(string $className) : bool
    {
        if (class_exists($className, false)) {
            return false;
        }

        if (!$this->classNameResolver->isDecoratorClassName($className)) {
            return false;
        }

        $file = $this->fileNameResolver->resolve($className);

        if (!file_exists($file)) {
            return false;
        }

        /* @noinspection PhpIncludeInspection */
        /* @noinspection UsingInclusionOnceReturnValueInspection */
        return (bool) require_once $file;
    }
}
