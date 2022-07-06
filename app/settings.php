<?php

declare(strict_types=1);

use App\Application\Settings\Settings;
use App\Application\Settings\SettingsInterface;
use DI\ContainerBuilder;
use Monolog\Logger;

return function (ContainerBuilder $containerBuilder) {

    // Global Settings Object
    $containerBuilder->addDefinitions([
        SettingsInterface::class => function () {
            return new Settings([
                'displayErrorDetails' => true, // Should be set to false in production
                'logError'            => false,
                'logErrorDetails'     => false,
                'logger' => [
                    'name' => 'slim-app',
                    'path' => isset($_ENV['docker']) ? 'php://stdout' : __DIR__ . '/../logs/app.log',
                    'level' => Logger::DEBUG,
                ],
                'doctrine' => [
                    // If true, metadata caching is forcefully disabled
                    'dev_mode'  => true,

                    // Path where the compiled metadata info will be cached
                    // Make sure the path exists and it is writable
                    'cache_dir' =>   '../var/cache/doctrine',

                    'proxy_dir'     => '../var/proxies',
//
//                    // You should add any other path containing annotated entity classes
                    'metadata_dirs' => ['../src'],

                    'connection' => [
                        'driver'   => 'pdo_mysql',
                        'host'     => 'slim-mariadb',
                        'port'     => 3306,
                        'dbname'   => 'slim',
                        'user'     => 'user',
                        'password' => 'password',
                        'charset'  => 'utf8mb4'
                    ]
                ]
            ]);
        }
    ]);
};
