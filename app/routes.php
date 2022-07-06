<?php

declare(strict_types=1);

use Doctrine\ORM\EntityManager;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;

return function (App $app) {
    $app->options('/{routes:.*}', function (Request $request, Response $response) {
        // CORS Pre-Flight OPTIONS Request Handler
        return $response;
    });

    $app->get('/', function (Request $request, Response $response) use ($app) {

        /** @var EntityManager $entityManager */
        $entityManager = $app->getContainer()->get(EntityManager::class);

        $entityManager->getConnection()->fetchAllAssociative('show tables');

        $json = json_encode(['hello world'], JSON_PRETTY_PRINT);
        $response->getBody()->write($json);

        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus(200);
    });
};
