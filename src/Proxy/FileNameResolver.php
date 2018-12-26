<?php

namespace Proxy;

class FileNameResolver
{
    private $proxyDir;

    public function __construct($proxyDir)
    {
        $this->proxyDir = $proxyDir;
    }

    public function resolve(string $className)
    {
        return
            $this->proxyDir
            . DIRECTORY_SEPARATOR
            . str_replace('\\', '_', $className)
            . '.php';
    }
}
