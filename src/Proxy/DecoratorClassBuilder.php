<?php

namespace Proxy;

use ReflectionClass;
use Zend\Code\Generator\ClassGenerator;
use Zend\Code\Generator\MethodGenerator;
use Zend\Code\Generator\PropertyGenerator;
use Zend\Code\Reflection\MethodReflection;

final class DecoratorClassBuilder
{
    private $classNameResolver;
    private $fileNameResolver;

    public function __construct(FileNameResolver $fileNameResolver, ClassNameResolver $classNameResolver)
    {
        $this->fileNameResolver = $fileNameResolver;
        $this->classNameResolver = $classNameResolver;
    }

    public function build(string $originalClassName)
    {
        $reflection = new ReflectionClass($originalClassName);
        $phpClass = new ClassGenerator();

        $phpClass->setImplementedInterfaces($reflection->getInterfaceNames());

        $className = $this->createName($originalClassName);
        $classNamespace = $this->createNamespaceName($reflection);
        $phpClass->setName($className);
        $phpClass->setNamespaceName($classNamespace);
        $phpClass->setFinal(true);

        $property = new PropertyGenerator();
        $property->setName('decoration');
        $property->setVisibility('private');
        $phpClass->addPropertyFromGenerator($property);

        $property = new PropertyGenerator();
        $property->setName('inner');
        $property->setVisibility('inner');
        $phpClass->addPropertyFromGenerator($property);

        $constructor = new MethodGenerator('__construct');
        $constructor->setParameters(['inner' , 'decoration']);
        $constructor->setBody(<<<'PHP'
$this->inner = $inner;
$this->decoration = $decoration;        
PHP
);
        $phpClass->addMethodFromGenerator($constructor);

        $behaveMethod = MethodGenerator::fromReflection(new MethodReflection($originalClassName, 'behave'));
        $behaveMethod->setBody(<<<'PHP'
return
    $this->decoration->withDecoration(
        $request,
        function ($request) {
            return $this->inner->behave($request);
        }
    );
PHP
        );
        $phpClass->addMethodFromGenerator($behaveMethod);


        $this->writePhpClass($phpClass);

        return $className;
    }

    private function writePhpClass(ClassGenerator $classGenerator)
    {
        $fullClassName = $classGenerator->getNamespaceName() . '\\' . $classGenerator->getName();
        $fileName = $this->getFileName($fullClassName);
        $generatedCode = $classGenerator->generate();

        $this->writeFile("<?php\n\n" . $generatedCode, $fileName);
    }

    private function writeFile(string $source, string $location) : void
    {
        $tmpFileName = @tempnam($location, 'temporaryProxyManagerFile');

        file_put_contents($tmpFileName, $source);

        if (! rename($tmpFileName, $location)) {
            unlink($tmpFileName);
        }
    }

    private function getFileName(string $fullClassName)
    {
        return $this->fileNameResolver->resolve($fullClassName);
    }

    private function createName(string $originalClassName)
    {
        return $this->classNameResolver->resolve($originalClassName);
    }

    private function createNamespaceName(ReflectionClass $reflectionClass)
    {
        return  $reflectionClass->getNamespaceName();
    }
}
