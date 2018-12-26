<?php

namespace Proxy;

use ProxyManager\Configuration;
use ProxyManager\Factory\LazyLoadingValueHolderFactory;
use ProxyManager\FileLocator\FileLocator;
use ProxyManager\GeneratorStrategy\FileWriterGeneratorStrategy;
use ProxyManager\Proxy\LazyLoadingInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\LazyProxy\Instantiator\InstantiatorInterface;

/**
 * Runtime lazy loading proxy generator.
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 */
class CacheInstantiator implements InstantiatorInterface
{
    private $factory;

    public function __construct()
    {
        $config = new Configuration();

        $fileLocator = new FileLocator(__DIR__ . '/../../cache');
        $config->setGeneratorStrategy(new FileWriterGeneratorStrategy($fileLocator));
        $config->setProxiesTargetDir(__DIR__ . '/../../cache');
        spl_autoload_register($config->getProxyAutoloader());

        $this->factory = new LazyLoadingValueHolderFactory($config);
    }

    /**
     * {@inheritdoc}
     */
    public function instantiateProxy(ContainerInterface $container, Definition $definition, $id, $realInstantiator)
    {
        return $this->factory->createProxy(
            $definition->getClass(),
            function (&$wrappedInstance, LazyLoadingInterface $proxy) use ($realInstantiator) {
                $wrappedInstance = \call_user_func($realInstantiator);

                $proxy->setProxyInitializer(null);

                return true;
            }
        );
    }
}
