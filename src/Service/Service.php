<?php

namespace Service;

class Service implements ServiceInterface {

    public function __construct()
    {
        echo "instantiated Service\n";
    }

    public function behave(string $request): int {
        echo 'Hello world. Request: ' . $request . PHP_EOL;

        return 123;
    }
}
