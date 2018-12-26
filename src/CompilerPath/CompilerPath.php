<?php

namespace CompilerPath;

use Proxy\Autoloader;
use Proxy\ClassNameResolver;
use Proxy\DecoratorClassBuilder;
use Proxy\FileNameResolver;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class CompilerPath implements CompilerPassInterface
{
    private $classNameResolver;
    private $fileNameResolver;
    private $decorators = [];

    public function __construct()
    {
        $this->classNameResolver = new ClassNameResolver();
        $this->fileNameResolver = new FileNameResolver(__DIR__ . '/../../cache');
        spl_autoload_register(new Autoloader($this->fileNameResolver, $this->classNameResolver));
    }

    public function process(ContainerBuilder $container)
    {
        $services = $container->findTaggedServiceIds('decorators');

        foreach ($services as $service => $tags) {

            usort($tags, function ($a, $b) { return $a['priority'] <=> $b['priority']; });

            foreach ($tags as $tag) {
                $this->decorate($container, $service, $tag);
            }
        }
    }

    private function decorate(ContainerBuilder $container, string $service, array $tag)
    {
        $definition = $container->getDefinition($service);
        $decoratorClass = $this->getDecorator($definition->getClass());

        $decoratorId = $service . '.' . $tag['id'];
        $decoratorDefinition =
            new Definition(
                $decoratorClass,
                [
                    new Reference($decoratorId . '.inner'),
                    new Reference($tag['logic'])
                ]
            );
        $decoratorDefinition->setDecoratedService($service);
        $container->addDefinitions([$decoratorId => $decoratorDefinition]);
    }

    private function getDecorator(string $originalClassName)
    {
        if (isset($this->decorators[$originalClassName])) {
            return $this->decorators[$originalClassName];
        }

        $decoratorClass = $this->classNameResolver->resolve($originalClassName);

        if (class_exists($decoratorClass)) {
            return $this->decorators[$originalClassName] = $decoratorClass;
        }

        $decoratorClassBuilder = new DecoratorClassBuilder($this->fileNameResolver, $this->classNameResolver);
        $decoratorClass = $decoratorClassBuilder->build($originalClassName);

        return $this->decorators[$originalClassName] = $decoratorClass;
    }
}
