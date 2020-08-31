<?php

namespace JSoumelidisTest\SymfonyDI\Config;

use Generator;
use JSoumelidis\SymfonyDI\Config\Config;
use JSoumelidis\SymfonyDI\Config\ContainerFactory;
use Psr\Container\ContainerInterface;
use stdClass;
use Symfony\Component\DependencyInjection\Alias;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Zend\ContainerConfigTest\AbstractExpressiveContainerConfigTest;
use Zend\ContainerConfigTest\TestAsset\Delegator;
use Zend\ContainerConfigTest\TestAsset\DelegatorFactory;
use Zend\ContainerConfigTest\TestAsset\Factory;
use Zend\ContainerConfigTest\TestAsset\Service;

class ContainerTest extends AbstractExpressiveContainerConfigTest
{
    /**
     * @param array $config
     * @param bool  $servicesAsSynthetic
     *
     * @return ContainerBuilder
     */
    protected function createContainer(array $config, bool $servicesAsSynthetic = false) : ContainerInterface
    {
        $factory = new ContainerFactory();
        $container = $factory(new Config(['dependencies' => $config], $servicesAsSynthetic));

        //Everything should work with compiled container also
        $container->compile();

        return $container;
    }

    public function testServicesAsSyntheticRegister(): void
    {
        $dependencies = [
            'services' => [
                Service::class => $service = new Service(),
            ],
        ];

        $container = $this->createContainer($dependencies, true);

        $this->assertTrue($container->has(Service::class));
        $this->assertTrue($container->getDefinition(Service::class)->isSynthetic());
    }

    /**
     * @expectedException \Exception
     */
    public function testServicesAsSyntheticCannotBeFetched(): void
    {
        $dependencies = [
            'services' => [
                Service::class => $service = new Service(),
            ],
        ];

        $container = $this->createContainer($dependencies, true);

        $container->get(Service::class);
    }

    public function testStaticMethodCallAsDelegatorFactory(): void
    {
        $dependencies = [
            'invokables' => [
                Service::class,
            ],
            'delegators' => [
                Service::class => [
                    [Assets\DelegatorFactory::class, 'create']
                ],
            ],
        ];

        $container = $this->createContainer($dependencies);

        $this->assertTrue($container->has(Service::class));

        $object = $container->get(Service::class);
        $this->assertInstanceOf(Service::class, $object);
        $this->assertInstanceOf(ContainerInterface::class, $object->injected[0]);
    }

    public function testObjectMethodCallableAsFactory(): void
    {
        $dependencies = [
            'factories' => [
                Service::class => [new Factory(), '__invoke'],
            ],
        ];

        $container = $this->createContainer($dependencies);

        $this->assertTrue($container->has(Service::class));

        $object = $container->get(Service::class);

        $this->assertInstanceOf(Service::class, $object);
    }

    public function testObjectMethodCallableAsFactoryUsingSyntheticServices(): void
    {
        $dependencies = [
            'factories' => [
                Service::class => [new Factory(), '__invoke'],
            ],
        ];

        $container = $this->createContainer($dependencies, true);

        $this->assertTrue($container->has(Service::class));
        $this->expectExceptionMessage(
            'You have requested a synthetic service '.
            '("smsfbridge.Zend\ContainerConfigTest\TestAsset\Service.factory.service"). '.
            'The DIC does not know how to construct this service.'
        );

        $container->get(Service::class);
    }

    public function testObjectAsFactory(): void
    {
        $dependencies = [
            'factories' => [
                Service::class => new Factory(),
            ],
        ];

        $container = $this->createContainer($dependencies);

        $this->assertTrue($container->has(Service::class));

        $object = $container->get(Service::class);
        $this->assertInstanceOf(Service::class, $object);
    }

    public function testObjectAsFactoryUsingSyntheticServices(): void
    {
        $dependencies = [
            'factories' => [
                Service::class => new Factory(),
            ],
        ];

        $container = $this->createContainer($dependencies, true);

        $this->assertTrue($container->has(Service::class));
        $this->expectExceptionMessage(
            'You have requested a synthetic service '.
            '("smsfbridge.Zend\ContainerConfigTest\TestAsset\Service.factory.service"). '.
            'The DIC does not know how to construct this service.'
        );

        $container->get(Service::class);
    }

    public function testObjectMethodCallableAsDelegatorFactory(): void
    {
        $dependencies = [
            'invokables' => [
                Service::class,
            ],
            'delegators' => [
                Service::class => [
                    [new DelegatorFactory(), '__invoke'],
                ],
            ],
        ];

        $container = $this->createContainer($dependencies);

        $this->assertTrue($container->has(Service::class));

        $object = $container->get(Service::class);

        $this->assertInstanceOf(Delegator::class, $object);
        $this->assertInstanceOf(\Closure::class, $callback = $object->callback);
        $this->assertInstanceOf(Service::class, $callback());
    }

    public function testObjectMethodCallableAsDelegatorFactoryUsingSyntheticServices(): void
    {
        $dependencies = [
            'invokables' => [
                Service::class,
            ],
            'delegators' => [
                Service::class => [
                    [new DelegatorFactory(), '__invoke'],
                ],
            ],
        ];

        $container = $this->createContainer($dependencies, true);

        $this->assertTrue($container->has(Service::class));
        $this->expectExceptionMessage(
            'You have requested a synthetic service '.
            '("smsfbridge.Zend\ContainerConfigTest\TestAsset\Service.delegator.0.service"). '.
            'The DIC does not know how to construct this service.'
        );

        $container->get(Service::class);
    }

    public function testObjectAsDelegatorFactory(): void
    {
        $dependencies = [
            'invokables' => [
                Service::class,
            ],
            'delegators' => [
                Service::class => [
                    new DelegatorFactory(),
                ],
            ],
        ];

        $container = $this->createContainer($dependencies);

        $this->assertTrue($container->has(Service::class));

        $object = $container->get(Service::class);

        $this->assertInstanceOf(Delegator::class, $object);
        $this->assertInstanceOf(\Closure::class, $callback = $object->callback);
        $this->assertInstanceOf(Service::class, $callback());
    }

    public function testObjectAsDelegatorFactoryUsingSyntheticServices(): void
    {
        $dependencies = [
            'invokables' => [
                Service::class,
            ],
            'delegators' => [
                Service::class => [
                    new DelegatorFactory(),
                ],
            ],
        ];

        $container = $this->createContainer($dependencies, true);

        $this->assertTrue($container->has(Service::class));
        $this->expectExceptionMessage(
            'You have requested a synthetic service '.
            '("smsfbridge.Zend\ContainerConfigTest\TestAsset\Service.delegator.0.service"). '.
            'The DIC does not know how to construct this service.'
        );

        $container->get(Service::class);
    }

    /**
     * @expectedException \UnexpectedValueException
     */
    public function testDoesNotAcceptDelegatorOnUndefinedServices(): void
    {
        $dependencies = [
            'delegators' => [
                'myservice' => [
                    DelegatorFactory::class,
                ],
            ],
        ];

        $this->createContainer($dependencies);
    }

    /**
     * @throws \Exception
     */
    public function testCreatesPrivateDelegatorForPublicAliasedPrivateServices(): void
    {
        $builder = new ContainerBuilder();

        //Given a private service with id Service::class
        $serviceDefinition = $builder
            ->register(Service::class, Service::class)
            ->setPublic(false);

        //Given a public alias 'myalias' to Service::class service id
        $alias = new Alias(Service::class);
        $alias->setPublic(true);

        $builder->setAlias('myalias', $alias);

        $dependencies = [
            'delegators' => [
                Service::class => [
                    DelegatorFactory::class,
                ],
            ],
        ];

        $config = new Config(['dependencies' => $dependencies]);
        $config->configureContainerBuilder($builder);

        $this->assertTrue($builder->hasDefinition(Service::class));

        $definition = $builder->getDefinition(Service::class);

        $this->assertEquals($serviceDefinition->isPublic(), $definition->isPublic());

        $builder->compile();

        $this->assertFalse($builder->has(Service::class));
        $this->assertTrue($builder->has('myalias'));

        /** @var Delegator $service */
        $service = $builder->get('myalias');

        $this->assertInstanceOf(Delegator::class, $service);
        $this->assertInstanceOf(\Closure::class, $service->callback);
    }

    /**
     * @param array<string,mixed> $dependencies
     * @dataProvider nonSharedServices
     */
    public function testCreatesNonSharedInstanceOfService(array $dependencies, bool $servicesAsSynthetic, string $service): void
    {
        $builder = new ContainerBuilder();
        $config = new Config([
            'dependencies' => $dependencies,
        ], $servicesAsSynthetic);

        $config->configureContainerBuilder($builder);
        $builder->compile();

        $instance1 = $builder->get($service);
        $instance2 = $builder->get($service);

        self::assertNotEquals($instance1, $instance2);
    }

    public function nonSharedServices(): Generator
    {
        yield 'invokable' => [
            [
                'shared' => [
                    stdClass::class => false,
                ],
                'invokables' => [
                    stdClass::class => stdClass::class,
                ],
            ],
            false,
            stdClass::class,
        ];

        yield 'factory' => [
            [
                'shared' => [
                    stdClass::class => false,
                ],
                'factories' => [
                    stdClass::class => static function () {return new stdClass();},
                ],
            ],
            false,
            stdClass::class,
        ];

        yield 'alias' => [
            [
                'shared' => [
                    stdClass::class => false,
                ],
                'factories' => [
                    stdClass::class => static function () {return new stdClass();},
                ],
                'aliases' => [
                    'foo' => stdClass::class,
                ],
            ],
            false,
            'foo',
        ];
    }
}
