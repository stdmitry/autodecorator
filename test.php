<?php

use CompilerPath\CompilerPath;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

include("vendor/autoload.php");

$containerBuilder = new ContainerBuilder();
$loader = new YamlFileLoader($containerBuilder, new FileLocator(__DIR__));
$loader->load('services.yml');

$containerBuilder->addCompilerPass(new CompilerPath());
$containerBuilder->compile();

$service = $containerBuilder->get('my_service');
$service->behave('some data');


