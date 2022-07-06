<?php

declare(strict_types=1);

use App\Application\Settings\SettingsInterface;
use Clockwork\Authentication\NullAuthenticator;
use Clockwork\DataSource\DoctrineDataSource;
use Clockwork\Storage\FileStorage;
use DI\ContainerBuilder;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\Driver\AnnotationDriver;
use Doctrine\ORM\Mapping\UnderscoreNamingStrategy;
use Doctrine\ORM\ORMSetup;
use Doctrine\Persistence\Mapping\Driver\MappingDriverChain;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Monolog\Processor\UidProcessor;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;

return function (ContainerBuilder $containerBuilder) {
    $containerBuilder->addDefinitions([
        LoggerInterface::class => function (ContainerInterface $c) {
            $settings = $c->get(SettingsInterface::class);

            $loggerSettings = $settings->get('logger');
            $logger = new Logger($loggerSettings['name']);

            $processor = new UidProcessor();
            $logger->pushProcessor($processor);

            $handler = new StreamHandler($loggerSettings['path'], $loggerSettings['level']);
            $logger->pushHandler($handler);

            return $logger;
        },

        EntityManager::class => static function (ContainerInterface $c) {

        /** @var SettingsInterface $settings */
        $settings = $c->get(SettingsInterface::class);

            $config = ORMSetup::createAnnotationMetadataConfiguration(
                $settings->get('doctrine')['metadata_dirs'],
                $settings->get('doctrine')['dev_mode'],
                $settings->get('doctrine')['proxy_dir']
            );

            $config->setProxyNamespace('App\Proxies');
            $config->setAutoGenerateProxyClasses(true);

            // Doctrine lowercase naming strategy
            $config->setNamingStrategy(new UnderscoreNamingStrategy());

            // Doctrine Gedmo annotation reader
            $annotationReader = new AnnotationReader();

            $driverChain = new MappingDriverChain();


            $annotationDriver = new AnnotationDriver(
                $annotationReader, // our cached annotation reader
                $settings->get('doctrine')['metadata_dirs'] // paths to look in
            );

            $driverChain->addDriver($annotationDriver, 'Entity');

            $config->setMetadataDriverImpl(
                new AnnotationDriver(
                    $annotationReader,
                    $settings->get('doctrine')['metadata_dirs']
                )
            );

            $provider = new FilesystemAdapter('', 0,  $settings->get('doctrine')['cache_dir']);

            $config->setMetadataCache($provider);
            $config->setQueryCache($provider);

            $evm = new Doctrine\Common\EventManager();

            AnnotationRegistry::registerLoader('class_exists');

            $entityManager = EntityManager::create(
                $settings->get('doctrine')['connection'],
                $config,
                $evm
            );

            /** @var \Clockwork\Clockwork $clock */
            $clock = $c->get('clockwork');
            $clock->addDataSource(new DoctrineDataSource($entityManager));

            return $entityManager;
        },
    ]);
};
