<?php

namespace Decorator;

use Closure;

class HelloDecoration
{
    /**
     * @var string
     */
    private $name;

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    public function withDecoration($request, Closure $closure)
    {
        echo "Hello from decorator $this->name\n";

        $result = $closure($request);

        echo "Goodbye from decorator $this->name\n";

        return $result;
    }
}
